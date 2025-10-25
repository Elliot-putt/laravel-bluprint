<?php

namespace App\Http\Controllers;

use App\Services\GitHubService;
use App\Services\JiraService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StandupController extends Controller
{
    public function index(Request $request, GitHubService $gitHubService, JiraService $jiraService)
    {
        $user = auth()->user();

        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        $githubData = $gitHubService->getYesterdayActivity($user, $yesterdayStart, $yesterdayEnd);
        $jiraData = $jiraService->getYesterdayActivity($user, $yesterdayStart, $yesterdayEnd);

        $standupData = [
            'summary' => $user->yesterdays_summary,
            'github' => $githubData,
            'jira' => $jiraData,
            'metrics' => $this->calculateMetrics($githubData, $jiraData),
            'timeline' => $this->buildTimeline($githubData, $jiraData),
            'charts' => $this->prepareChartData($githubData, $jiraData)
        ];

        return Inertia::render('Standup', $standupData);
    }

    private function calculateMetrics(array $githubData, array $jiraData): array
    {
        return [
            'prs_merged' => [
                'count' => count(array_get($githubData, 'merged_prs', [])),
                'details' => array_get($githubData, 'merged_prs', [])
            ],
            'prs_opened' => [
                'count' => count(array_get($githubData, 'opened_prs', [])),
                'details' => array_get($githubData, 'opened_prs', [])
            ],
            'tickets_transitioned' => [
                'count' => count(array_get($jiraData, 'tickets_transitioned', [])),
                'details' => array_get($jiraData, 'tickets_transitioned', [])
            ],
            'code_reviews' => [
                'count' => count(array_get($githubData, 'reviews_completed', [])),
                'details' => array_get($githubData, 'reviews_completed', [])
            ],
            'commits_made' => array_get($githubData, 'total_commits', 0),
            'review_comments' => array_get($githubData, 'review_comments_count', 0)
        ];
    }

    private function buildTimeline(array $githubData, array $jiraData): array
    {
        $timeline = [];

        foreach (array_get($githubData, 'merged_prs', []) as $pr) {
            $timeline[] = [
                'type' => 'pr_merged',
                'title' => 'Merged PR: ' . array_get($pr, 'title', ''),
                'description' => array_get($pr, 'repository_full_name', ''),
                'time' => array_get($pr, 'mergedAt', ''),
                'icon' => 'check',
                'color' => 'green'
            ];
        }

        foreach (array_get($githubData, 'opened_prs', []) as $pr) {
            $timeline[] = [
                'type' => 'pr_opened',
                'title' => 'Opened PR: ' . array_get($pr, 'title', ''),
                'description' => array_get($pr, 'repository_full_name', ''),
                'time' => array_get($pr, 'createdAt', ''),
                'icon' => 'plus',
                'color' => 'blue'
            ];
        }

        foreach (array_get($githubData, 'reviews_completed', []) as $review) {
            $timeline[] = [
                'type' => 'review_completed',
                'title' => 'Reviewed: ' . array_get($review, 'pr_title', ''),
                'description' => 'Provided feedback on ' . array_get($review, 'repository_full_name', ''),
                'time' => array_get($review, 'submitted_at', ''),
                'icon' => 'eye',
                'color' => 'pink'
            ];
        }

        foreach (array_get($jiraData, 'tickets_transitioned', []) as $ticket) {
            $mostRecentTransition = null;
            $transitions = array_get($ticket, 'status_transitions', []);

            if (!empty($transitions)) {
                $mostRecentTransition = collect($transitions)->sortByDesc(function ($transition) {
                    $timestamp = array_get($transition, 'changed_at');
                    return $timestamp ? strtotime($timestamp) : 0;
                })->first();
            }

            $transitionText = $mostRecentTransition
                ? (array_get($mostRecentTransition, 'from_status', 'Unknown') . ' → ' . array_get($mostRecentTransition, 'to_status', 'Unknown'))
                : 'Status changed';

            $timeToUse = $mostRecentTransition && array_get($mostRecentTransition, 'changed_at')
                ? $mostRecentTransition['changed_at']
                : array_get($ticket, 'fields.updated', '');

            $timeline[] = [
                'type' => 'ticket_transitioned',
                'title' => 'Transitioned Ticket: ' . array_get($ticket, 'key', 'Unknown'),
                'description' => $transitionText . ' • ' . array_get($ticket, 'fields.summary', 'No summary'),
                'time' => $timeToUse,
                'icon' => 'tag',
                'color' => 'yellow'
            ];
        }

        usort($timeline, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($timeline, 0, 10);
    }

    private function prepareChartData(array $githubData, array $jiraData): array
    {
        return [
            'velocity' => [
                'labels' => ['PRs Merged', 'PRs Opened', 'PRs Ready for Review', 'Drafts'],
                'data' => [
                    count(array_get($githubData, 'merged_prs', [])),
                    count(array_get($githubData, 'opened_prs', [])),
                    count(array_get($githubData, 'prs_ready_for_review', [])),
                    count(array_get($githubData, 'draft_prs', []))
                ]
            ],
            'reviews' => [
                'labels' => ['Approved', 'Commented', 'Requested Changes', 'Dismissed'],
                'data' => [
                    array_get($githubData, 'reviews_approved', 0),
                    array_get($githubData, 'reviews_commented', 0),
                    array_get($githubData, 'reviews_changes_requested', 0),
                    array_get($githubData, 'reviews_dismissed', 0)
                ]
            ]
        ];
    }
}
