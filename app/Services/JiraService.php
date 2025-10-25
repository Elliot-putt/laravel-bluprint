<?php

namespace App\Services;

use App\Data\JiraTicketData;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class JiraService
{
    private function client(User $user): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->withBasicAuth($user->jira_connected_email, $user->jira_token)
          ->baseUrl("https://{$user->jira_domain}");

    }

    public function getTicketInformation(User $user, string $ticketKey)
    {
        if (! $ticketKey || !$user->jira_token || !$user->jira_domain || !$user->jira_connected_email) {
            return [];
        }

        $response = $this->client($user)->get("/rest/api/3/issue/{$ticketKey}");

        if (! $response->successful()) {
            return [];
        }

        return $response->json();

    }

    public function jiraContext(User $user, string|null $ticketKey): string
    {
        if (!$user->hasConfiguredJira() || !$ticketKey) {
            return '';
        }

        $ticketInfo = $this->getTicketInformation($user, $ticketKey);

        $title = array_get($ticketInfo, 'fields.summary', 'No Title');
        $link = array_get($ticketInfo, 'self', '');
        $description = '';

        if ($descContent = array_get($ticketInfo, 'fields.description.content')) {
            foreach ($descContent as $content) {
                if ($textContent = array_get($content, 'content')) {
                    foreach ($textContent as $item) {
                        $description .= array_get($item, 'text', '') . ' ';
                    }
                    $description .= "\n";
                }
            }
        }

        return "Title: {$title}\nLink: {$link}\nDescription: {$description}\n Ticket Key: {$ticketKey}\n";
    }

    public function getJiraTicketsInProgress(User $user): Collection
    {
        if (!$user->hasConfiguredJira()) {
            return collect();
        }

        $response = $this->client($user)->get('/rest/api/3/search', [
            'jql' => 'assignee=currentUser() AND status != "Done"',
            'startAt' => 0,
            'maxResults' => 50,
            'fields' => 'key,summary,status,assignee,priority,created,updated,issuetype'
        ]);

        if (!$response->successful()) {
            return collect();
        }

        $issues = $response->json('issues');
        return JiraTicketData::collect(collect($issues)->map(function ($issue) {
            return new JiraTicketData(
                key: array_get($issue, 'key'),
                summary: array_get($issue, 'fields.summary'),
                status: array_get($issue, 'fields.status.name'),
                assignee: array_get($issue, 'fields.assignee.displayName'),
                priority: array_get($issue, 'fields.priority.name'),
                created: array_get($issue, 'fields.created'),
                updated: array_get($issue, 'fields.updated'),
                issueType: array_get($issue, 'fields.issuetype.name'),
            );
        }));
    }

    public function getYesterdayActivity(User $user, Carbon $startDate, Carbon $endDate): array
    {
        if (!$user->hasConfiguredJira()) {
            return $this->getEmptyJiraActivityData();
        }

        $cacheKey = "jira_yesterday_activity_{$user->id}_{$startDate->format('Y-m-d')}";

        $cached = Cache::get($cacheKey);
        //        if ($cached !== null) {
        //            return $cached;
        //        }

        $activity = $this->fetchYesterdayJiraActivity($user, $startDate, $endDate);

        Cache::put($cacheKey, $activity, 300);

        return $activity;
    }

    private function fetchYesterdayJiraActivity(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $activity = $this->getEmptyJiraActivityData();

        $startDateJQL = $startDate->format('Y-m-d H:i');
        $endDateJQL = $endDate->format('Y-m-d H:i');

        $ticketsStarted = $this->getTicketsStarted($user, $startDateJQL, $endDateJQL);
        $ticketsUpdated = $this->getTicketsUpdated($user, $startDateJQL, $endDateJQL);
        $ticketsCompleted = $this->getTicketsCompleted($user, $startDateJQL, $endDateJQL);
        $ticketsTransitioned = $this->getTicketsTransitioned($user, $startDateJQL, $endDateJQL);

        $activity['tickets_started'] = $ticketsStarted;
        $activity['tickets_updated'] = $ticketsUpdated;
        $activity['tickets_completed'] = $ticketsCompleted;
        $activity['tickets_transitioned'] = $ticketsTransitioned;

        $activity['total_tickets_worked'] = count(array_unique(array_merge(
            array_column($ticketsStarted, 'key'),
            array_column($ticketsUpdated, 'key'),
            array_column($ticketsCompleted, 'key'),
            array_column($ticketsTransitioned, 'key')
        )));

        return $activity;
    }

    private function getTicketsStarted(User $user, string $startDate, string $endDate): array
    {
        $jql = 'assignee=currentUser() AND created >= "' . $startDate . '" AND created <= "' . $endDate . '"';

        $response = $this->client($user)->get('/rest/api/3/search', [
            'jql' => $jql,
            'startAt' => 0,
            'maxResults' => 50,
            'fields' => 'key,summary,status,assignee,priority,created,updated,issuetype,description'
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('issues', []);
    }

    private function getTicketsUpdated(User $user, string $startDate, string $endDate): array
    {
        $jql = 'assignee=currentUser() AND updated >= "' . $startDate . '" AND updated <= "' . $endDate . '" AND created < "' . $startDate . '"';

        $response = $this->client($user)->get('/rest/api/3/search', [
            'jql' => $jql,
            'startAt' => 0,
            'maxResults' => 50,
            'fields' => 'key,summary,status,assignee,priority,created,updated,issuetype,description'
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('issues', []);
    }

    private function getTicketsCompleted(User $user, string $startDate, string $endDate): array
    {
        $jql = 'assignee=currentUser() AND status changed to "Done" DURING ("' . $startDate . '", "' . $endDate . '")';

        $response = $this->client($user)->get('/rest/api/3/search', [
            'jql' => $jql,
            'startAt' => 0,
            'maxResults' => 50,
            'fields' => 'key,summary,status,assignee,priority,created,updated,issuetype,description'
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('issues', []);
    }

    private function getTicketsTransitioned(User $user, string $startDate, string $endDate): array
    {
        $jql = 'assignee=currentUser() AND status changed DURING ("' . $startDate . '", "' . $endDate . '")';

        $response = $this->client($user)->get('/rest/api/3/search', [
            'jql' => $jql,
            'startAt' => 0,
            'maxResults' => 50,
            'fields' => 'key,summary,status,assignee,priority,created,updated,issuetype,description',
            'expand' => 'changelog'
        ]);

        if (!$response->successful()) {
            return [];
        }

        $issues = $response->json('issues', []);

        foreach ($issues as &$issue) {
            $issue['status_transitions'] = $this->extractStatusTransitions($issue, $startDate, $endDate, $user);
            $issue['jira_url'] = "https://{$user->jira_domain}/browse/{$issue['key']}";
        }

        return $issues;
    }

    private function extractStatusTransitions(array $issue, string $startDate, string $endDate, User $user): array
    {
        $transitions = [];
        $changelog = $issue['changelog']['histories'] ?? [];

        foreach ($changelog as $history) {
            $historyDate = $history['created'] ?? '';

            if (!$this->isDateInRange($historyDate, $startDate, $endDate)) {
                continue;
            }

            foreach ($history['items'] ?? [] as $item) {
                if ($item['field'] === 'status') {
                    $transitions[] = [
                        'from_status' => $item['fromString'] ?? 'Unknown',
                        'to_status' => $item['toString'] ?? 'Unknown',
                        'changed_at' => $historyDate,
                        'author' => $history['author']['displayName'] ?? 'Unknown'
                    ];
                }
            }
        }

        return $transitions;
    }

    private function isDateInRange(string $dateString, string $startDate, string $endDate): bool
    {
        try {
            $date = new \DateTime($dateString);
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);

            return $date >= $start && $date <= $end;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getEmptyJiraActivityData(): array
    {
        return [
            'tickets_started' => [],
            'tickets_updated' => [],
            'tickets_completed' => [],
            'tickets_transitioned' => [],
            'total_tickets_worked' => 0
        ];
    }

    public function transitionTicketStatus(User $user, string $ticketKey, bool $isDraft): array
    {
        if (!$user->hasConfiguredJira() || !$ticketKey) {
            return ['success' => false, 'message' => 'Jira not configured or no ticket key provided'];
        }

        $targetStatus = $isDraft ? $user->jira_in_progress_status : $user->jira_review_status;
        
        // First, get the available transitions for this ticket
        $transitionsResponse = $this->client($user)->get("/rest/api/3/issue/{$ticketKey}/transitions");
        
        if (!$transitionsResponse->successful()) {
            return ['success' => false, 'message' => 'Failed to get ticket transitions'];
        }

        $transitions = $transitionsResponse->json('transitions', []);
        $targetTransition = null;

        // Find the transition that matches our target status
        foreach ($transitions as $transition) {
            if ($transition['to']['name'] === $targetStatus) {
                $targetTransition = $transition;
                break;
            }
        }

        if (!$targetTransition) {
            return ['success' => false, 'message' => "No transition found to status: {$targetStatus}"];
        }

        // Execute the transition
        $transitionResponse = $this->client($user)->post("/rest/api/3/issue/{$ticketKey}/transitions", [
            'transition' => [
                'id' => $targetTransition['id']
            ]
        ]);

        if (!$transitionResponse->successful()) {
            return ['success' => false, 'message' => 'Failed to transition ticket'];
        }

        return [
            'success' => true, 
            'message' => "Ticket {$ticketKey} transitioned to {$targetStatus}",
            'ticket_key' => $ticketKey,
            'new_status' => $targetStatus
        ];
    }

}
