<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AIService;
use App\Services\GitHubService;
use App\Services\JiraService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StandupSummaryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(GitHubService $gitHubService, JiraService $jiraService, AIService $aiService): void
    {
        try {
            Log::info("Starting standup summary generation for user: {$this->user->id}");

            if (!$aiService->isAvailable()) {
                Log::warning("AI service is not available for user: {$this->user->id}");
                return;
            }

            $yesterdayStart = Carbon::yesterday()->startOfDay();
            $yesterdayEnd = Carbon::yesterday()->endOfDay();

            $githubData = [];
            $jiraData = [];

            if ($this->user->github_access_token) {
                $githubData = $gitHubService->getYesterdayActivity($this->user, $yesterdayStart, $yesterdayEnd);
            }

            if ($this->user->hasConfiguredJira()) {
                $jiraData = $jiraService->getYesterdayActivity($this->user, $yesterdayStart, $yesterdayEnd);
            }

            $hasActivity = $this->hasSignificantActivity($githubData, $jiraData);

            if (!$hasActivity) {
                $this->user->update([
                    'yesterdays_summary' => $this->generateQuietDaySummary()
                ]);
                Log::info("Generated quiet day summary for user: {$this->user->id}");
                return;
            }

            $activityContext = $this->buildActivityContext($githubData, $jiraData);
            $summary = $this->generateAISummary($aiService, $activityContext, $githubData, $jiraData);

            $this->user->update([
                'yesterdays_summary' => $summary
            ]);

            Log::info("Successfully generated AI standup summary for user: {$this->user->id}");

        } catch (\Exception $e) {
            Log::error("Failed to generate standup summary for user: {$this->user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->user->update([
                'yesterdays_summary' => $this->generateErrorSummary()
            ]);
        }
    }

    private function hasSignificantActivity(array $githubData, array $jiraData): bool
    {
        return count(array_get($githubData, 'merged_prs', [])) > 0 ||
            count(array_get($githubData, 'opened_prs', [])) > 0 ||
            count(array_get($githubData, 'reviews_completed', [])) > 0 ||
            count(array_get($jiraData, 'tickets_transitioned', [])) > 0 ||
            array_get($githubData, 'total_commits', 0) > 0;
    }

    private function buildActivityContext(array $githubData, array $jiraData): string
    {
        $context = "User's yesterday activity data:\n\n";

        $hasGitHub = !empty($githubData);
        $hasJira = !empty($jiraData) && count(array_get($jiraData, 'tickets_transitioned', [])) > 0;

        if ($hasGitHub) {
            $context .= "GITHUB ACTIVITY:\n";
            $context .= '- PRs Merged: ' . count(array_get($githubData, 'merged_prs', [])) . "\n";
            $context .= '- PRs Opened: ' . count(array_get($githubData, 'opened_prs', [])) . "\n";
            $context .= '- Code Reviews: ' . count(array_get($githubData, 'reviews_completed', [])) . "\n";
            $context .= '- Total Commits: ' . array_get($githubData, 'total_commits', 0) . "\n";

            // Analyze PR relationships for better flow
            $mergedPRs = array_get($githubData, 'merged_prs', []);
            $openedPRs = array_get($githubData, 'opened_prs', []);

            // Check for PRs that were both opened and merged same day
            $quickTurnaroundPRs = [];
            $standaloneOpened = [];
            $standaloneMerged = [];

            foreach ($openedPRs as $openedPR) {
                $foundMerged = false;
                foreach ($mergedPRs as $mergedPR) {
                    // Match by title similarity or same repository + similar timing
                    if ($this->areSimilarPRs($openedPR, $mergedPR)) {
                        $quickTurnaroundPRs[] = [
                            'opened' => $openedPR,
                            'merged' => $mergedPR
                        ];
                        $foundMerged = true;
                        break;
                    }
                }
                if (!$foundMerged) {
                    $standaloneOpened[] = $openedPR;
                }
            }

            // Find merged PRs that weren't opened today
            foreach ($mergedPRs as $mergedPR) {
                $wasOpenedToday = false;
                foreach ($quickTurnaroundPRs as $pair) {
                    if ($this->areSimilarPRs($mergedPR, $pair['merged'])) {
                        $wasOpenedToday = true;
                        break;
                    }
                }
                if (!$wasOpenedToday) {
                    $standaloneMerged[] = $mergedPR;
                }
            }

            if (!empty($quickTurnaroundPRs)) {
                $context .= "\nQuick Turnaround PRs (opened and merged same day):\n";
                foreach ($quickTurnaroundPRs as $pair) {
                    $context .= "- Opened and merged: \"{$pair['opened']['title']}\" in {$pair['opened']['repository_full_name']}\n";
                }
            }

            if (!empty($standaloneMerged)) {
                $context .= "\nStandalone Merged PRs:\n";
                foreach ($standaloneMerged as $pr) {
                    $context .= "- \"{$pr['title']}\" in {$pr['repository_full_name']}\n";
                }
            }

            if (!empty($standaloneOpened)) {
                $context .= "\nStandalone Opened PRs:\n";
                foreach ($standaloneOpened as $pr) {
                    $context .= "- \"{$pr['title']}\" in {$pr['repository_full_name']}\n";
                }
            }

            if (!empty(array_get($githubData, 'reviews_completed', []))) {
                $context .= "\nCode Reviews Completed:\n";
                foreach (array_get($githubData, 'reviews_completed', []) as $review) {
                    $context .= "- {$review['state']} review on \"{$review['pr_title']}\" in {$review['repository_full_name']}\n";
                }
            }
        } else {
            $context .= "GITHUB ACTIVITY: No GitHub integration configured\n";
        }

        if ($hasJira) {
            $context .= "\nJIRA ACTIVITY:\n";
            $context .= '- Tickets Transitioned: ' . count(array_get($jiraData, 'tickets_transitioned', [])) . "\n";

            if (!empty(array_get($jiraData, 'tickets_transitioned', []))) {
                $context .= "\nTransitioned Tickets:\n";
                foreach (array_get($jiraData, 'tickets_transitioned', []) as $ticket) {
                    $summary = array_get($ticket, 'fields.summary', 'No title');
                    $context .= "- {$ticket['key']}: {$summary}\n";

                    if (!empty($ticket['status_transitions'])) {
                        $mostRecent = collect($ticket['status_transitions'])->sortByDesc('changed_at')->first();
                        if ($mostRecent) {
                            $context .= "  Status: {$mostRecent['from_status']} → {$mostRecent['to_status']}\n";
                        }
                    }
                }
            }
        } else {
            $context .= "\nJIRA ACTIVITY: No JIRA integration configured\n";
        }

        return $context;
    }

    private function areSimilarPRs(array $pr1, array $pr2): bool
    {
        // Check if they're for the same ticket number
        preg_match('/([A-Z]+-\d+)/', $pr1['title'], $matches1);
        preg_match('/([A-Z]+-\d+)/', $pr2['title'], $matches2);

        if (!empty($matches1) && !empty($matches2) && $matches1[1] === $matches2[1]) {
            return true;
        }

        // Check if titles are very similar (more than 70% similar)
        $similarity = 0;
        similar_text(strtolower($pr1['title']), strtolower($pr2['title']), $similarity);

        return $similarity > 70;
    }

    private function generateAISummary(AIService $aiService, string $activityContext, array $githubData, array $jiraData): string
    {
        $hasJira = !empty($jiraData) && count(array_get($jiraData, 'tickets_transitioned', [])) > 0;
        $integrationNote = $hasJira ? 'User has both GitHub and JIRA integrations.' : 'User has GitHub integration only (no JIRA data available).';

        $prompt = "Generate a professional standup summary for yesterday's work activity. Use the provided activity data to create an engaging summary that highlights the developer's accomplishments.

{$integrationNote}

CRITICAL REQUIREMENTS FOR FLOW AND READABILITY:
- ALWAYS mention specific PR titles when referencing PRs (not just numbers)
- ALWAYS mention specific ticket keys AND their summaries when referencing tickets (if JIRA data available)
- When saying 'X PRs merged' or 'Y tickets transitioned', ALWAYS specify which ones by name/title
- Be specific about what work was done, don't use generic terms
- If no JIRA data is available, focus entirely on GitHub activity and don't mention JIRA at all
- Write in NATURAL, CONVERSATIONAL language - NO straight quotes around titles
- Use natural phrasing like 'merged the SPWEB-578 PR for adjusting heading sizes' instead of 'merged \"SPWEB-578: title\"'
- Reference work naturally as if explaining to a colleague

SMART FLOW REQUIREMENTS:
- Look for 'Quick Turnaround PRs' that were opened and merged same day - mention these as a cohesive flow
- Group related activities naturally (e.g., 'I merged the X PR and the Y PR, then opened another PR for Z')
- Use transitional words like 'Also', 'Then', 'Additionally' to create natural flow
- When multiple PRs relate to the same ticket, mention that connection
- Make the summary read like a story of the day's work, not a list of disconnected tasks

FORMATTING REQUIREMENTS:
- Use HTML with Tailwind CSS classes
- Use these specific color classes for highlights:
  * text-purple-400 font-semibold for productivity levels (highly productive, productive, etc.)
  * text-green-400 font-semibold for positive achievements (PRs merged, tickets completed)
  * text-blue-400 font-semibold for work started (PRs opened, tickets started)
  * text-pink-400 font-semibold for collaborative work (code reviews, feedback)
  * text-yellow-400 font-semibold for ticket transitions (only if JIRA data available)
  * text-zinc-200 for regular text
- Keep the summary concise but informative (2-3 sentences maximum)
- Include specific numbers and brief mentions of key work items
- Write in past tense describing what was accomplished yesterday
- NO emojis
- Structure as a single paragraph with appropriate spans for highlighting

EXAMPLES OF GOOD NATURAL FLOW:
- 'Yesterday was productive. I merged the SPWEB-578 PR for adjusting heading sizes and the PR to store default content directly. Also, I opened another PR for SPWEB-578 to adjust heading sizes.'
- 'Had a busy day with SPWEB-597 work. I opened and merged a PR for the initial implementation, then transitioned the ticket to In Progress.'
- 'Focused on code reviews yesterday, providing feedback on 3 different PRs, and also moved SPWEB-578 (Title leading) to Anna & Josh's review.'

{$activityContext}

Generate a summary that flows naturally and tells the story of the day's work while keeping it brief and professional.";

        try {
            $response = $aiService->client()->post('', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $generatedText = array_get($responseData, 'candidates.0.content.parts.0.text', '');

                if (!empty($generatedText)) {
                    return $this->cleanAIResponse($generatedText);
                }
            }

        } catch (\Exception $e) {
            Log::error("AI summary generation failed for user: {$this->user->id}", [
                'error' => $e->getMessage()
            ]);
        }

        return $this->generateFallbackSummary($githubData, $jiraData);
    }

    private function cleanAIResponse(string $response): string
    {
        $cleaned = trim($response);
        $cleaned = preg_replace('/^```html\s*|\s*```$/m', '', $cleaned);
        $cleaned = preg_replace('/^```\s*|\s*```$/m', '', $cleaned);

        return $cleaned;
    }

    private function generateFallbackSummary(array $githubData, array $jiraData): string
    {
        $prsCount = count(array_get($githubData, 'merged_prs', []));
        $openedCount = count(array_get($githubData, 'opened_prs', []));
        $reviewsCount = count(array_get($githubData, 'reviews_completed', []));
        $ticketsCount = count(array_get($jiraData, 'tickets_transitioned', []));

        $totalActivity = $prsCount + $openedCount + $reviewsCount + $ticketsCount;

        if ($totalActivity === 0) {
            return $this->generateQuietDaySummary();
        }

        $productivityLevel = $totalActivity >= 5 ? 'highly productive' : ($totalActivity >= 3 ? 'productive' : 'steady');

        $summary = '<p class="text-zinc-200">Yesterday was a <span class="text-purple-400 font-semibold">' . $productivityLevel . '</span> day';

        if ($prsCount > 0) {
            $mergedPRs = array_get($githubData, 'merged_prs', []);
            if (count($mergedPRs) == 1) {
                $summary .= ' with <span class="text-green-400 font-semibold">1 PR merged</span> for ' . $this->cleanTitle($mergedPRs[0]['title']);
            } else {
                $prTitles = collect($mergedPRs)->take(2)->map(function ($pr) {
                    return $this->cleanTitle($pr['title']);
                })->toArray();
                $prText = implode(' and ', $prTitles);
                if (count($mergedPRs) > 2) {
                    $prText .= ' plus ' . (count($mergedPRs) - 2) . ' other' . (count($mergedPRs) > 3 ? 's' : '');
                }
                $summary .= ' with <span class="text-green-400 font-semibold">' . $prsCount . ' PRs merged</span> including ' . $prText;
            }
        }

        if ($openedCount > 0) {
            $openedPRs = array_get($githubData, 'opened_prs', []);
            if (count($openedPRs) == 1) {
                $summary .= ($prsCount > 0 ? ' and' : ' with') . ' <span class="text-blue-400 font-semibold">1 new PR opened</span> for ' . $this->cleanTitle($openedPRs[0]['title']);
            } else {
                $prTitles = collect($openedPRs)->take(2)->map(function ($pr) {
                    return $this->cleanTitle($pr['title']);
                })->toArray();
                $prText = implode(' and ', $prTitles);
                if (count($openedPRs) > 2) {
                    $prText .= ' plus ' . (count($openedPRs) - 2) . ' other' . (count($openedPRs) > 3 ? 's' : '');
                }
                $summary .= ($prsCount > 0 ? ' and' : ' with') . ' <span class="text-blue-400 font-semibold">' . $openedCount . ' new PRs opened</span> including ' . $prText;
            }
        }

        if ($reviewsCount > 0) {
            $summary .= '. Completed <span class="text-pink-400 font-semibold">' . $reviewsCount . ' code review' . ($reviewsCount > 1 ? 's' : '') . '</span>';
        }

        if ($ticketsCount > 0) {
            $tickets = array_get($jiraData, 'tickets_transitioned', []);
            if (count($tickets) == 1) {
                $ticket = $tickets[0];
                $summary .= ' and moved <span class="text-yellow-400 font-semibold">' . $ticket['key'] . ' (' . array_get($ticket, 'fields.summary', 'No title') . ')</span> to a new status';
            } else {
                $ticketDetails = collect($tickets)->take(2)->map(function ($ticket) {
                    return $ticket['key'] . ' (' . array_get($ticket, 'fields.summary', 'No title') . ')';
                })->toArray();
                $ticketText = implode(' and ', $ticketDetails);
                if (count($tickets) > 2) {
                    $ticketText .= ' plus ' . (count($tickets) - 2) . ' other' . (count($tickets) > 3 ? 's' : '');
                }
                $summary .= ' and progressed <span class="text-yellow-400 font-semibold">' . $ticketsCount . ' JIRA tickets</span>: ' . $ticketText;
            }
        }

        $summary .= '.</p>';

        return $summary;
    }

    private function cleanTitle(string $title): string
    {
        // Remove ticket numbers from the beginning if present
        $cleaned = preg_replace('/^[A-Z]+-\d+:\s*/', '', $title);

        // Make it more conversational
        $cleaned = strtolower($cleaned);

        return $cleaned;
    }

    private function generateQuietDaySummary(): string
    {
        return '<p class="text-zinc-200">Yesterday was a <span class="text-blue-400 font-semibold">quiet day</span> with no recorded GitHub or JIRA activity. Perhaps focused on planning, meetings, or local development work.</p>';
    }

    private function generateErrorSummary(): string
    {
        return '<p class="text-zinc-200">Unable to generate yesterday\'s summary due to a technical issue. Please check your integrations.</p>';
    }
}
