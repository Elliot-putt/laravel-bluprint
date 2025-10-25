<?php

namespace App\Services;

use App\Data\BranchData;
use App\Data\LabelData;
use App\Data\PullRequestData;
use App\Data\RecentBranchData;
use App\Data\RepositoryData;
use App\Data\ReviewerData;
use App\Models\User;
use App\Services\JiraService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    // Set token limit (400,000 tokens ≈ 1,600,000 characters)
    private const MAX_DIF_LENGTH = 1600000;

    protected string $baseUrl = 'https://api.github.com';

    private function client(User $user): PendingRequest
    {
        return Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->withToken($user->github_access_token)
            ->baseUrl($this->baseUrl);
    }

    public function getBranches(User $user, array $data): Collection
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');

        if (!$repoName || !$repoOwner) {
            return Collection::empty();
        }

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/branches", [
            'per_page' => 100,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return Collection::empty();
        }

        return BranchData::collect(collect($response->json())->map(function ($branch) use ($repoName, $repoOwner) {
            return new BranchData(
                name: array_get($branch, 'name'),
                sha: array_get($branch, 'commit.sha'),
                repository: $repoName,
                repositoryOwner: $repoOwner,
                repositoryFullName: "{$repoOwner}/{$repoName}",
                protected: array_get($branch, 'protected', false),
            );
        }));
    }

    public function getBranchesPaginated(User $user, array $data): array
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');
        $page = array_get($data, 'page', 1);
        $perPage = array_get($data, 'per_page', 100);

        if (!$repoName || !$repoOwner) {
            return [
                'data' => Collection::empty(),
                'pagination' => [
                    'current_page' => 1,
                    'has_more' => false,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ];
        }

        $params = [
            'per_page' => $perPage,
            'page' => $page,
        ];

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/branches", $params);

        if ($response->failed()) {
            return [
                'data' => Collection::empty(),
                'pagination' => [
                    'current_page' => $page,
                    'has_more' => false,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ];
        }

        $branches = collect($response->json());

        $branchData = BranchData::collect($branches->map(function ($branch) use ($repoName, $repoOwner) {
            return new BranchData(
                name: array_get($branch, 'name'),
                sha: array_get($branch, 'commit.sha'),
                repository: $repoName,
                repositoryOwner: $repoOwner,
                repositoryFullName: "{$repoOwner}/{$repoName}",
                protected: array_get($branch, 'protected', false),
            );
        }));

        // Check if there are more pages by checking if we got a full page
        $hasMore = $branches->count() >= $perPage;

        return [
            'data' => $branchData,
            'pagination' => [
                'current_page' => $page,
                'has_more' => $hasMore,
                'per_page' => $perPage,
                'total' => $branches->count()
            ]
        ];
    }

    public function getRepositories(User $user): Collection
    {
        $response = $this->client($user)->get('/user/repos', [
            'per_page' => 100,
            'page' => 1,
        ]);

        $repos = $response->json();

        return RepositoryData::collect(collect($repos)->map(function ($repo) {
            return new RepositoryData(
                id: array_get($repo, 'id'),
                name: array_get($repo, 'name'),
                owner: array_get($repo, 'owner.login'),
                fullName: array_get($repo, 'full_name'),
                description: array_get($repo, 'description'),
                link: array_get($repo, 'html_url'),
                defaultBranch: array_get($repo, 'default_branch'),
            );
        }));
    }

    public function getRepositoriesPaginated(User $user, array $data): array
    {
        $page = array_get($data, 'page', 1);
        $perPage = array_get($data, 'per_page', 100);

        $params = [
            'per_page' => $perPage,
            'page' => $page,
        ];

        $response = $this->client($user)->get('/user/repos', $params);

        if ($response->failed()) {
            return [
                'data' => Collection::empty(),
                'pagination' => [
                    'current_page' => $page,
                    'has_more' => false,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ];
        }

        $repos = collect($response->json());
        $hasMore = $repos->count() >= $perPage;

        $repoData = RepositoryData::collect($repos->map(function ($repo) {
            return new RepositoryData(
                id: array_get($repo, 'id'),
                name: array_get($repo, 'name'),
                owner: array_get($repo, 'owner.login'),
                fullName: array_get($repo, 'full_name'),
                description: array_get($repo, 'description'),
                link: array_get($repo, 'html_url'),
                defaultBranch: array_get($repo, 'default_branch'),
            );
        }));

        return [
            'data' => $repoData,
            'pagination' => [
                'current_page' => $page,
                'has_more' => $hasMore,
                'per_page' => $perPage,
                'total' => $repos->count()
            ]
        ];
    }

    public function getLabels(User $user, array $data = []): Collection
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');

        if (!$repoName || !$repoOwner) {
            return Collection::empty();
        }

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/labels", [
            'per_page' => 100,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return Collection::empty();
        }

        return LabelData::collect(collect($response->json())->map(function ($label) {
            return new LabelData(
                id: array_get($label, 'id'),
                name: array_get($label, 'name'),
                color: array_get($label, 'color'),
            );
        }));
    }

    public function getReviewers(User $user, array $data = []): Collection
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');

        if (!$repoName || !$repoOwner) {
            return Collection::empty();
        }

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/collaborators", [
            'per_page' => 100,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return Collection::empty();
        }

        return ReviewerData::collect(collect($response->json())->map(function ($reviewer) {
            return new ReviewerData(
                id: array_get($reviewer, 'id'),
                name: array_get($reviewer, 'name', array_get($reviewer, 'login', '')),
                login: array_get($reviewer, 'login', ''),
            );
        }));
    }

    public function getBranchCodeDifference(User $user, array $data): string | null
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');
        $baseBranchSha = array_get($data, 'base_branch_sha');
        $targetBranchSha = array_get($data, 'target_branch_sha');

        if (!$repoName || !$repoOwner || !$baseBranchSha || !$targetBranchSha) {
            return null;
        }

        return $this->compileCodeDifferenceToString($this->client($user)->get("/repos/{$repoOwner}/{$repoName}/compare/{$baseBranchSha}...{$targetBranchSha}")->json());
    }

    public function isCurrentlyGitHubPR(User $user, array $data): bool
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');
        $baseBranch = array_get($data, 'base_branch');
        $targetBranch = array_get($data, 'target_branch');

        if (!$repoName || !$repoOwner || !$baseBranch || !$targetBranch) {
            return false;
        }

        $head = "{$repoOwner}:{$targetBranch}";

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/pulls", [
            'base' => $baseBranch,
            'head' => $head,
            'state' => 'open',
        ]);

        $pulls = $response->json();
        return is_array($pulls) && count($pulls) > 0;
    }

    public function getPR(User $user, array $data): array | null
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');
        $baseBranch = array_get($data, 'base_branch');
        $targetBranch = array_get($data, 'target_branch');

        if (!$repoName || !$repoOwner || !$baseBranch || !$targetBranch) {
            return [];
        }
        $head = "{$repoOwner}:{$targetBranch}";

        $response = $this->client($user)->get("/repos/{$repoOwner}/{$repoName}/pulls", [
            'base' => $baseBranch,
            'head' => $head,
            'state' => 'open',
        ]);

        $pulls = $response->json();

        if (is_array($pulls) && count($pulls) > 0) {
            return $pulls[0];
        }

        return null;
    }

    private function compileCodeDifferenceToString(array $data)
    {
        $output = '';
        $charCount = 0;
        $filesProcessed = 0;
        $totalFiles = isset($data['files']) && is_array($data['files']) ? count($data['files']) : 0;

        // Add basic info about the comparison
        $basicInfo = "Comparison between commits:\n" .
            'Status: ' . ($data['status'] ?? 'unknown') . "\n" .
            'Ahead by: ' . ($data['ahead_by'] ?? '0') . " commits\n" .
            'Behind by: ' . ($data['behind_by'] ?? '0') . " commits\n\n";

        $output .= $basicInfo;
        $charCount += strlen($basicInfo);

        // Add information about changed files
        if ($totalFiles > 0) {
            $filesHeader = 'Changed files (' . $totalFiles . "):\n" .
                "---------------------\n\n";
            $output .= $filesHeader;
            $charCount += strlen($filesHeader);

            foreach ($data['files'] as $file) {
                $status = $file['status'] ?? 'unknown';
                $filename = $file['filename'] ?? 'unknown';
                $additions = $file['additions'] ?? 0;
                $deletions = $file['deletions'] ?? 0;

                // Calculate size of this file entry before adding it
                $fileHeader = "{$filename} ({$status})\n" .
                    "  +{$additions} -{$deletions}\n";

                $filePatch = '';
                if (isset($file['patch'])) {
                    $filePatch = "\n" . $file['patch'] . "\n\n";
                } else {
                    $filePatch = "\n  (No patch available)\n\n";
                }

                $fileSeparator = "---------------------\n\n";
                $totalFileSize = strlen($fileHeader) + strlen($filePatch) + strlen($fileSeparator);

                // Check if adding this file would exceed the limit
                if ($charCount + $totalFileSize > self::MAX_DIF_LENGTH) {
                    // Add a note about truncated files
                    $remainingFilesMsg = "\n\n--- TRUNCATED ---\n" .
                        'Diff too large. ' . ($totalFiles - $filesProcessed) .
                        " more files not shown.\n";

                    if ($charCount + strlen($remainingFilesMsg) <= self::MAX_DIF_LENGTH) {
                        $output .= $remainingFilesMsg;
                    }

                    break;
                }

                // Add this file's content to the output
                $output .= $fileHeader . $filePatch . $fileSeparator;
                $charCount += $totalFileSize;
                $filesProcessed++;
            }
        } else {
            $noFilesMsg = "No files changed.\n";
            $output .= $noFilesMsg;
            $charCount += strlen($noFilesMsg);
        }

        return $output;
    }

    public function submitPR(User $user, array $data): array
    {
        $repoName = array_get($data, 'repository');
        $repoOwner = array_get($data, 'repository_owner');
        $title = array_get($data, 'title');
        $body = array_get($data, 'body');
        $head = array_get($data, 'head');
        $base = array_get($data, 'base');
        $labels = array_get($data, 'labels');
        $reviewers = array_get($data, 'reviewers');
        $isCurrentlyGitHubPR = array_get($data, 'isCurrentlyGitHubPR');
        $isDraft = array_get($data, 'isDraft', false);
        $jiraTicketKey = array_get($data, 'jira_ticket_key');

        if (!$repoName || !$repoOwner || !$title || !$body || !$head || !$base) {
            return ['pr_url' => '', 'jira_transition' => null];
        }
        $prNumber = null;
        $response = [];

        if ($isCurrentlyGitHubPR) {
            $pr = $this->getPR($user, [
                'repository' => $repoName,
                'repository_owner' => $repoOwner,
                'base_branch' => $base,
                'target_branch' => $head,
            ]);

            if (array_get($pr, 'number')) {
                $response = $this->client($user)->patch("/repos/{$repoOwner}/{$repoName}/pulls/" . array_get($pr, 'number'), [
                    'title' => $title,
                    'body' => $body,
                    'head' => $head,
                    'base' => $base,
                ]);

                $prNumber = array_get($response->json(), 'number');
            }
        } else {
            $requestData = [
                'title' => $title,
                'body' => $body,
                'head' => $head,
                'base' => $base,
                'draft' => $isDraft,
            ];

            $response = $this->client($user)->post("/repos/{$repoOwner}/{$repoName}/pulls", $requestData);

            $prNumber = array_get($response->json(), 'number');
        }
        if ($prNumber && collect($labels)->isNotEmpty()) {
            $this->client($user)->post("/repos/{$repoOwner}/{$repoName}/issues/{$prNumber}/labels", [
                'labels' => $labels,
            ]);
        }

        if ($prNumber && collect($reviewers)->isNotEmpty()) {
            $this->client($user)->post("/repos/{$repoOwner}/{$repoName}/pulls/{$prNumber}/requested_reviewers", [
                'reviewers' => $reviewers,
            ]);
        }

        $prUrl = array_get($response->json(), 'html_url');
        $jiraTransition = null;

        // Handle Jira ticket transition if ticket key is provided
        if ($jiraTicketKey && $user->hasConfiguredJira()) {
            $jiraService = app(JiraService::class);
            $jiraTransition = $jiraService->transitionTicketStatus($user, $jiraTicketKey, $isDraft);
        }

        return [
            'pr_url' => $prUrl,
            'jira_transition' => $jiraTransition
        ];
    }

    public function getRecentlyMadeBranches(User $user, int $hoursBack = 24, bool $includeMainBranches = false): Collection
    {
        $cacheKey = "recent_branches_gql_{$user->id}_{$hoursBack}_" . ($includeMainBranches ? '1' : '0');

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return RecentBranchData::collect(collect($cached)->map(function ($branch) {
                return [
                    'repository' => $branch['repository'],
                    'repositoryOwner' => $branch['repository_owner'],
                    'repositoryFullName' => $branch['repository_full_name'],
                    'name' => $branch['name'],
                    'sha' => $branch['sha'],
                    'lastCommitDate' => $branch['last_commit_date'],
                    'lastCommitMessage' => $branch['last_commit_message'],
                    'lastCommitAuthor' => $branch['last_commit_author'],
                    'branchCreationDate' => $branch['branch_creation_date'],
                    'hasOpenPr' => $branch['has_open_pr'],
                    'isRecentlyCreated' => $branch['is_recently_created'],
                    'hoursSinceLastCommit' => $branch['hours_since_last_commit'],
                    'suggestedForPr' => $branch['suggested_for_pr'],
                    'protected' => $branch['protected'] ?? false,
                ];
            }));
        }

        $activeRepos = $this->getActiveRepositories($user);

        if (empty($activeRepos)) {
            return Collection::empty();
        }

        $recentBranches = $this->fetchRecentBranchesWithGraphQL($user, $activeRepos, $hoursBack, $includeMainBranches);

        Cache::put($cacheKey, $recentBranches, 120);

        // Map snake_case to camelCase before passing to RecentBranchData::collect
        $mappedBranches = collect($recentBranches)->map(function ($branch) {
            return [
                'repository' => $branch['repository'],
                'repositoryOwner' => $branch['repository_owner'],
                'repositoryFullName' => $branch['repository_full_name'],
                'name' => $branch['name'],
                'sha' => $branch['sha'],
                'lastCommitDate' => $branch['last_commit_date'],
                'lastCommitMessage' => $branch['last_commit_message'],
                'lastCommitAuthor' => $branch['last_commit_author'],
                'branchCreationDate' => $branch['branch_creation_date'],
                'hasOpenPr' => $branch['has_open_pr'],
                'isRecentlyCreated' => $branch['is_recently_created'],
                'hoursSinceLastCommit' => $branch['hours_since_last_commit'],
                'suggestedForPr' => $branch['suggested_for_pr'],
                'protected' => $branch['protected'] ?? false,
            ];
        });

        return RecentBranchData::collect($mappedBranches);
    }

    private function fetchRecentBranchesWithGraphQL(User $user, array $repositories, int $hoursBack, bool $includeMainBranches): array
    {
        $cutoffDate = Carbon::now()->subHours($hoursBack)->toISOString();
        $mainBranches = ['main', 'master', 'develop', 'dev', 'staging', 'production'];

        $chunks = array_chunk($repositories, 5);
        $allBranches = [];

        foreach ($chunks as $chunk) {
            $repoQueries = [];

            foreach ($chunk as $index => $repo) {
                $repoQueries[] = "
                repo{$index}: repository(owner: \"{$repo['owner']}\", name: \"{$repo['name']}\") {
                    refs(refPrefix: \"refs/heads/\", first: 100, orderBy: {field: TAG_COMMIT_DATE, direction: DESC}) {
                        nodes {
                            name
                            target {
                                ... on Commit {
                                    oid
                                    committedDate
                                    pushedDate
                                    author {
                                        user {
                                            login
                                        }
                                        name
                                        email
                                    }
                                    message
                                    history(first: 10, since: \"{$cutoffDate}\") {
                                        nodes {
                                            oid
                                            committedDate
                                            author {
                                                user {
                                                    login
                                                }
                                                name
                                                email
                                            }
                                            message
                                        }
                                    }
                                    associatedPullRequests(first: 5) {
                                        nodes {
                                            number
                                            state
                                            author {
                                                login
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ";
            }

            $query = '
            query {
                ' . implode("\n", $repoQueries) . '
            }
        ';

            $response = $this->executeGraphQLQuery($user, $query);

            if (!$response->successful()) {
                continue;
            }

            $data = $response->json();
            if (isset($data['errors'])) {
                \Log::error('GraphQL recent branches query errors', ['errors' => $data['errors']]);
                continue;
            }

            foreach ($chunk as $index => $repo) {
                $repoKey = "repo{$index}";
                $repoData = $data['data'][$repoKey] ?? null;

                if (!$repoData || !isset($repoData['refs']['nodes'])) {
                    continue;
                }

                foreach ($repoData['refs']['nodes'] as $ref) {
                    $branchName = $ref['name'];
                    $commit = $ref['target'];

                    if (!$includeMainBranches && in_array(strtolower($branchName), $mainBranches)) {
                        continue;
                    }

                    if (!$this->isBranchRecentForUser($user, $commit, $cutoffDate)) {
                        continue;
                    }

                    $hasOpenPRByUser = $this->hasOpenPRByUser($user, $commit['associatedPullRequests']['nodes'] ?? []);

                    $commitDate = Carbon::parse($commit['pushedDate'] ?? $commit['committedDate']);

                    $allBranches[] = [
                        'repository' => $repo['name'],
                        'repository_owner' => $repo['owner'],
                        'repository_full_name' => $repo['full_name'],
                        'name' => $branchName,
                        'sha' => $commit['oid'],
                        'last_commit_date' => $commitDate->toISOString(),
                        'last_commit_message' => $commit['message'] ?? '',
                        'last_commit_author' => $commit['author']['name'] ?? '',
                        'branch_creation_date' => $commitDate->toISOString(),
                        'has_open_pr' => $hasOpenPRByUser,
                        'is_recently_created' => $commitDate->greaterThan(Carbon::now()->subHours(24)),
                        'hours_since_last_commit' => $commitDate->diffInHours(Carbon::now()),
                        'suggested_for_pr' => !$hasOpenPRByUser,
                        'protected' => false,
                    ];
                }
            }
        }

        usort($allBranches, function ($a, $b) {
            return strtotime($b['last_commit_date']) - strtotime($a['last_commit_date']);
        });

        return $allBranches;
    }

    private function isBranchRecentForUser(User $user, array $commit, string $cutoffDate): bool
    {
        $commitDate = $commit['pushedDate'] ?? $commit['committedDate'] ?? null;
        if (!$commitDate) {
            return false;
        }

        if (Carbon::parse($commitDate)->lessThan(Carbon::parse($cutoffDate))) {
            return false;
        }

        $authorLogin = $commit['author']['user']['login'] ?? null;
        if ($authorLogin === $user->github_username) {
            return true;
        }

        $userCommits = $commit['history']['nodes'] ?? [];
        if (!empty($userCommits)) {
            foreach ($userCommits as $userCommit) {
                $userCommitAuthor = $userCommit['author']['user']['login'] ?? null;
                if ($userCommitAuthor === $user->github_username) {
                    $userCommitDate = Carbon::parse($userCommit['committedDate']);
                    return $userCommitDate->greaterThan(Carbon::parse($cutoffDate));
                }
            }
        }

        return false;
    }

    private function hasOpenPRByUser(User $user, array $pullRequests): bool
    {
        foreach ($pullRequests as $pr) {
            if (($pr['author']['login'] ?? null) === $user->github_username &&
                strtoupper($pr['state'] ?? '') === 'OPEN') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get active repositories from user's recent events
     */
    public function getActiveRepositories(User $user): array
    {
        $response = $this->client($user)->get("/users/{$user->github_username}/events", [
            'per_page' => 50,
        ]);

        $events = $response->json();
        if (!is_array($events)) {
            return [];
        }

        $uniqueRepos = [];
        $seenRepos = [];

        foreach ($events as $event) {
            if (!isset($event['repo']['name'])) {
                continue;
            }

            $repoFullName = $event['repo']['name'];

            if (in_array($repoFullName, $seenRepos)) {
                continue;
            }

            $seenRepos[] = $repoFullName;
            $repoParts = explode('/', $repoFullName);

            if (count($repoParts) === 2) {
                $uniqueRepos[] = [
                    'owner' => $repoParts[0],
                    'name' => $repoParts[1],
                    'id' => $event['repo']['id'] ?? null,
                    'full_name' => $repoFullName,
                ];
            }
        }

        return $uniqueRepos;
    }

    /**
     * Combine legacy status and check runs states, taking the "worst" status
     */
    private function combineStates(string $legacyState, string $checkRunsState): string
    {
        // Priority order: failure > error > pending > success > unknown
        $statePriority = [
            'failure' => 1,
            'error' => 2,
            'pending' => 3,
            'success' => 4,
            'unknown' => 5,
        ];

        $legacyPriority = $statePriority[$legacyState] ?? 5;
        $checkRunsPriority = $statePriority[$checkRunsState] ?? 5;

        // Return the state with higher priority (lower number = higher priority)
        if ($legacyPriority <= $checkRunsPriority) {
            return $legacyState;
        }
        return $checkRunsState;

    }

    /**
     * Determine overall state from check runs
     */
    private function determineOverallCheckState(array $checkRuns): string
    {
        if (empty($checkRuns)) {
            return 'unknown';
        }

        $conclusions = collect($checkRuns)->pluck('conclusion')->filter()->toArray();
        $statuses = collect($checkRuns)->pluck('status')->toArray();

        // If any are still running
        if (in_array('in_progress', $statuses) || in_array('queued', $statuses)) {
            return 'pending';
        }

        // If any failed or were cancelled
        if (in_array('failure', $conclusions) || in_array('cancelled', $conclusions)) {
            return 'failure';
        }

        // If any require action
        if (in_array('action_required', $conclusions)) {
            return 'pending';
        }

        // If all completed successfully
        if (!empty($conclusions)) {
            $successStates = ['success', 'neutral', 'skipped'];
            if (!array_diff($conclusions, $successStates)) {
                return 'success';
            }
        }

        return 'pending';
    }

    /**
     * Execute GraphQL query using user's token
     */
    private function executeGraphQLQuery(User $user, string $query, array $variables = []): \Illuminate\Http\Client\Response
    {
        return Http::withToken($user->github_access_token)
            ->withHeaders([
                'Accept' => 'application/vnd.github.v4+json',
                'User-Agent' => config('app.name') . '/1.0'
            ])
            ->timeout(30)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => $variables
            ]);
    }

    public function getAllPullRequestData(User $user, string|null $repoOwner, string|null $repoName, string $state = 'open', bool $showAll = false, int $perPage = 25, int $page = 1): LengthAwarePaginator
    {
        if (!$repoOwner || !$repoName) {

            return $this->createEmptyPaginator($perPage, $page);

        }

        $prNumbers = $this->getPRNumbersFromREST($user, $repoOwner, $repoName, $state, $showAll, $perPage, $page);

        if (empty($prNumbers['numbers'])) {
            return $this->createEmptyPaginator($perPage, $page);
        }

        $detailedPRs = $this->getDetailedPRsFromGraphQL($user, $repoOwner, $repoName, $prNumbers['numbers']);

        $items = [];
        foreach ($detailedPRs as $pr) {
            $processedPR = $this->processGraphQLPRData($pr, $repoOwner, $repoName, $user);
            $items[] = [
                'pull_request' => $processedPR['pull_request'],
                'build_status' => $processedPR['build_status'],
                'review_data' => $processedPR['review_data']
            ];
        }

        return $this->createPaginator($items, $prNumbers['total'], $perPage, $page);
    }

    private function getPRNumbersFromREST(User $user, string $repoOwner, string $repoName, string $state, bool $showAll, int $perPage, int $page): array
    {
        $searchQuery = "repo:{$repoOwner}/{$repoName} type:pr";

        if ($state !== 'all') {
            $searchQuery .= " state:{$state}";
        }

        if (!$showAll) {
            $searchQuery .= " author:{$user->github_username}";
        }

        $queryParams = [
            'q' => $searchQuery,
            'sort' => 'created',
            'order' => 'desc',
            'per_page' => $perPage,
            'page' => $page
        ];

        $response = $this->client($user)->get('/search/issues', $queryParams);

        if (!$response->successful()) {
            \Log::error('GitHub Search API failed', [
                'query' => $searchQuery,
                'response' => $response->body()
            ]);
            return ['numbers' => [], 'total' => 0];
        }

        $data = $response->json();
        if (!isset($data['items']) || !is_array($data['items'])) {
            return ['numbers' => [], 'total' => 0];
        }

        $numbers = [];
        foreach ($data['items'] as $item) {
            if (isset($item['number'])) {
                $numbers[] = $item['number'];
            }
        }

        $total = $data['total_count'] ?? count($numbers);

        return ['numbers' => $numbers, 'total' => $total];
    }

    private function getDetailedPRsFromGraphQL(User $user, string $repoOwner, string $repoName, array $prNumbers): array
    {
        if (empty($prNumbers)) {
            return [];
        }

        $prQueriesArray = [];
        foreach ($prNumbers as $index => $number) {
            $prQueriesArray[] = "pr{$index}: pullRequest(number: {$number}) { ...PullRequestFields }";
        }
        $prQueries = implode("\n", $prQueriesArray);

        $query = "
        fragment PullRequestFields on PullRequest {
            number
            title
            body
            state
            createdAt
            updatedAt
            mergedAt
            closedAt
            isDraft
            author {
                login
            }
            baseRefName
            baseRefOid
            headRefName
            headRefOid
            mergeable
            reviewDecision
            url

            baseRef {
                name
                target {
                    oid
                }
            }

            reviews(first: 50) {
                nodes {
                    id
                    state
                    author {
                        login
                    }
                    authorAssociation
                    submittedAt
                    commit {
                        oid
                    }
                }
            }

            reviewRequests(first: 20) {
                nodes {
                    requestedReviewer {
                        ... on User {
                            login
                        }
                        ... on Team {
                            name
                        }
                    }
                }
            }

            commits(last: 1) {
                nodes {
                    commit {
                        oid
                        pushedDate
                        status {
                            state
                            contexts {
                                context
                                state
                                targetUrl
                                description
                            }
                        }
                        checkSuites(first: 10) {
                            nodes {
                                status
                                conclusion
                                checkRuns(first: 50) {
                                    nodes {
                                        name
                                        status
                                        conclusion
                                        detailsUrl
                                        summary
                                    }
                                }
                            }
                        }
                    }
                }
            }

            labels(first: 20) {
                nodes {
                    name
                    color
                }
            }

            assignees(first: 10) {
                nodes {
                    login
                }
            }
        }

        query(\$owner: String!, \$repo: String!) {
            repository(owner: \$owner, name: \$repo) {
                {$prQueries}
            }
        }
    ";

        $variables = [
            'owner' => $repoOwner,
            'repo' => $repoName
        ];

        $response = $this->executeGraphQLQuery($user, $query, $variables);

        if (!$response->successful()) {
            \Log::error('GraphQL detailed PR query failed', [
                'response' => $response->body()
            ]);
            return [];
        }

        $data = $response->json();
        if (isset($data['errors'])) {
            \Log::error('GraphQL detailed PR query errors', [
                'errors' => $data['errors']
            ]);
            return [];
        }

        $repository = $data['data']['repository'] ?? [];
        $prs = [];

        foreach ($prNumbers as $index => $number) {
            $prKey = "pr{$index}";
            if (isset($repository[$prKey]) && $repository[$prKey] !== null) {
                $prs[] = $repository[$prKey];
            }
        }

        return $prs;
    }

    private function processGraphQLPRData(array $pr, string $repoOwner, string $repoName, User $user): array
    {
        $repoFullName = "{$repoOwner}/{$repoName}";
        $prAuthor = $pr['author']['login'] ?? null;

        $reviews = collect($pr['reviews']['nodes'] ?? []);
        $reviewRequests = collect($pr['reviewRequests']['nodes'] ?? []);
        $latestCommit = $pr['commits']['nodes'][0]['commit'] ?? null;

        $requestedReviewers = $reviewRequests
            ->map(fn ($request) => $request['requestedReviewer']['login'] ?? $request['requestedReviewer']['name'])
            ->filter()
            ->toArray();

        $pullRequest = PullRequestData::from([
            'number' => $pr['number'],
            'title' => $pr['title'],
            'body' => $pr['body'] ?? '',
            'state' => strtolower($pr['state']),
            'createdAt' => $pr['createdAt'],
            'updatedAt' => $pr['updatedAt'],
            'mergedAt' => $pr['mergedAt'],
            'closedAt' => $pr['closedAt'],
            'isDraft' => $pr['isDraft'] ?? false,
            'mergeable' => $pr['mergeable'],
            'url' => $pr['url'],
            'repositoryName' => $repoName,
            'repositoryOwner' => $repoOwner,
            'repositoryFullName' => $repoFullName,
            'user' => ['login' => $prAuthor],
            'head' => [
                'ref' => $pr['headRefName'],
                'sha' => $pr['headRefOid']
            ],
            'base' => [
                'ref' => $pr['baseRefName'],
                'sha' => $pr['baseRefOid']
            ],
            'labels' => collect($pr['labels']['nodes'] ?? [])->map(fn ($label) => [
                'name' => $label['name'],
                'color' => $label['color']
            ])->toArray(),
            'assignees' => collect($pr['assignees']['nodes'] ?? [])->pluck('login')->toArray(),
            'isMine' => $prAuthor === $user->github_username,
            'currentBaseSha' => $pr['baseRef']['target']['oid'] ?? null,
            'prBaseSha' => $pr['baseRefOid'],
            'isBehind' => strtolower($pr['state']) === 'open' ? (($pr['baseRef']['target']['oid'] ?? null) !== $pr['baseRefOid']) : false,
            'isUpToDate' => strtolower($pr['state']) === 'open' ? (($pr['baseRef']['target']['oid'] ?? null) === $pr['baseRefOid']) : true,
            'comments' => $pr['comments']['totalCount'] ?? null,
            'commits' => $pr['commits']['totalCount'] ?? null,
            'changedFiles' => $pr['changedFiles'] ?? null,
            'additions' => $pr['additions'] ?? null,
            'deletions' => $pr['deletions'] ?? null,
            'isMerged' => !empty($pr['mergedAt']),
        ]);

        $reviewData = [
            'pr_number' => $pr['number'],
            'title' => $pr['title'],
            'state' => strtolower($pr['state']),
            'author' => $prAuthor,
            'created_at' => Carbon::parse($pr['createdAt']),
            'updated_at' => Carbon::parse($pr['updatedAt']),
            'head_oid' => $pr['headRefOid'],
            'review_decision' => $pr['reviewDecision'],
            'reviews' => $reviews->map(fn ($review) => [
                'id' => $review['id'],
                'state' => $review['state'],
                'author' => $review['author']['login'] ?? null,
                'submitted_at' => Carbon::parse($review['submittedAt']),
                'commit_oid' => $review['commit']['oid'] ?? null,
                'author_association' => $review['authorAssociation']
            ])->toArray(),
            'requested_reviewers' => $requestedReviewers,
            'review_summary' => $this->analyzeReviewWorkflow($reviews, $requestedReviewers)
        ];

        $buildStatus = [
            'sha' => $pr['headRefOid'],
            'overall_state' => 'unknown',
            'statuses' => [],
            'check_runs' => [],
            'total_count' => 0,
            'repository_owner' => $repoOwner,
            'repository_name' => $repoName,
            'repository_full_name' => $repoFullName,
        ];

        if (strtolower($pr['state']) === 'open' && $latestCommit) {
            $legacyStatus = $latestCommit['status'] ?? null;
            $checkSuites = $latestCommit['checkSuites']['nodes'] ?? [];

            $legacyState = $legacyStatus['state'] ?? 'unknown';
            $legacyContexts = $legacyStatus['contexts'] ?? [];

            // Normalize legacy status contexts for frontend
            $normalizedLegacyContexts = array_map(function ($context) {
                return [
                    'context' => $context['context'] ?? '',
                    'state' => $context['state'] ?? '',
                    'target_url' => $context['targetUrl'] ?? $context['target_url'] ?? '',
                    'description' => $context['description'] ?? '',
                ];
            }, $legacyContexts);

            $allCheckRuns = [];
            foreach ($checkSuites as $suite) {
                $checkRuns = $suite['checkRuns']['nodes'] ?? [];
                // Map details_url/detailsUrl and ensure all keys are present for frontend compatibility
                foreach ($checkRuns as &$run) {
                    if (isset($run['details_url'])) {
                        $run['detailsUrl'] = $run['details_url'];
                    } elseif (isset($run['detailsUrl'])) {
                        $run['detailsUrl'] = $run['detailsUrl'];
                    }
                    // Ensure all expected keys exist
                    $run['name'] = $run['name'] ?? '';
                    $run['status'] = $run['status'] ?? '';
                    $run['conclusion'] = $run['conclusion'] ?? '';
                    $run['summary'] = $run['summary'] ?? '';
                }
                unset($run);
                $allCheckRuns = array_merge($allCheckRuns, $checkRuns);
            }

            $checkRunsState = $this->determineOverallCheckState($allCheckRuns);
            $overallState = $this->combineStates($legacyState, $checkRunsState);

            $buildStatus = array_merge($buildStatus, [
                'overall_state' => strtolower($overallState),
                'statuses' => $normalizedLegacyContexts,
                'check_runs' => $allCheckRuns,
                'total_count' => count($normalizedLegacyContexts) + count($allCheckRuns),
            ]);
        }

        return [
            'pull_request' => $pullRequest,
            'build_status' => $buildStatus,
            'review_data' => $reviewData
        ];
    }

    private function analyzeReviewWorkflow(Collection $reviews, array $requestedReviewers): array
    {
        if ($reviews->isEmpty() && empty($requestedReviewers)) {
            return [
                'workflow_status' => 'no_review_requested',
                'status_message' => 'No review requested',
                'approved_by' => [],
                'changes_requested_by' => [],
                'awaiting_review_from' => [],
                'needs_attention_from' => [],
            ];
        }

        if ($reviews->isEmpty() && !empty($requestedReviewers)) {
            return [
                'workflow_status' => 'awaiting_initial_review',
                'status_message' => 'Awaiting review from ' . implode(', ', $requestedReviewers),
                'approved_by' => [],
                'changes_requested_by' => [],
                'awaiting_review_from' => $requestedReviewers,
                'needs_attention_from' => $requestedReviewers,
            ];
        }

        $latestReviewsByUser = [];
        $commentedBy = [];

        foreach ($reviews as $review) {
            $userLogin = $review['author']['login'] ?? 'unknown';
            $submittedAt = $review['submittedAt'];

            if (!isset($latestReviewsByUser[$userLogin]) || $submittedAt > $latestReviewsByUser[$userLogin]['submittedAt']) {
                $latestReviewsByUser[$userLogin] = $review;
            }

            if (!in_array($userLogin, $commentedBy)) {
                $commentedBy[] = $userLogin;
            }
        }

        $approvedBy = [];
        $changesRequestedBy = [];

        foreach ($latestReviewsByUser as $userLogin => $review) {
            switch ($review['state']) {
                case 'APPROVED':
                    $approvedBy[] = $userLogin;
                    break;
                case 'CHANGES_REQUESTED':
                    $changesRequestedBy[] = $userLogin;
                    break;
            }
        }

        $awaitingReviewFrom = array_diff($requestedReviewers, array_keys($latestReviewsByUser));

        if (!empty($changesRequestedBy)) {
            $status = 'changes_requested';
            $message = 'Changes requested by ' . implode(', ', $changesRequestedBy);
            $needsAttention = ['author'];
        } elseif (!empty($approvedBy) && empty($awaitingReviewFrom)) {
            $status = 'fully_approved';
            $message = 'Approved by ' . implode(', ', $approvedBy);
            $needsAttention = ['author'];
        } elseif (!empty($approvedBy)) {
            $status = 'partially_approved';
            $message = 'Approved by ' . implode(', ', $approvedBy) . ', awaiting ' . implode(', ', $awaitingReviewFrom);
            $needsAttention = $awaitingReviewFrom;
        } elseif (!empty($awaitingReviewFrom)) {
            $status = 'awaiting_review';
            $message = 'Awaiting review from ' . implode(', ', $awaitingReviewFrom);
            $needsAttention = $awaitingReviewFrom;
        } else {
            $status = 'review_in_progress';
            if (!empty($commentedBy)) {
                $message = 'Review in progress (' . implode(', ', $commentedBy) . ' in conversations)';
            } else {
                $message = 'Review in progress';
            }
            $needsAttention = [];
        }

        return [
            'workflow_status' => $status,
            'status_message' => $message,
            'approved_by' => $approvedBy,
            'changes_requested_by' => $changesRequestedBy,
            'awaiting_review_from' => $awaitingReviewFrom,
            'needs_attention_from' => $needsAttention,
            'commented_by' => $commentedBy,
        ];
    }

    private function createPaginator(array $items, int $total, int $perPage, int $currentPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );
    }

    private function createEmptyPaginator(int $perPage, int $currentPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );
    }
    public function getYesterdayActivity(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "github_yesterday_activity_{$user->id}_{$startDate->format('Y-m-d')}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $activeRepos = $this->getActiveRepositories($user);

        if (empty($activeRepos)) {
            return $this->getEmptyActivityData();
        }

        $activity = $this->fetchYesterdayActivityWithGraphQL($user, $activeRepos, $startDate, $endDate);

        Cache::put($cacheKey, $activity, 300);

        return $activity;
    }

    private function fetchYesterdayActivityWithGraphQL(User $user, array $repositories, Carbon $startDate, Carbon $endDate): array
    {
        $startDateISO = $startDate->toISOString();
        $endDateISO = $endDate->toISOString();

        $chunks = array_chunk($repositories, 3);
        $allActivity = $this->getEmptyActivityData();

        foreach ($chunks as $chunk) {
            $repoQueries = [];

            foreach ($chunk as $index => $repo) {
                $repoQueries[] = "
            repo{$index}: repository(owner: \"{$repo['owner']}\", name: \"{$repo['name']}\") {
                pullRequests(first: 50, orderBy: {field: UPDATED_AT, direction: DESC}) {
                    nodes {
                        number
                        title
                        state
                        createdAt
                        updatedAt
                        mergedAt
                        closedAt
                        isDraft
                        url
                        author {
                            login
                        }
                        baseRefName
                        headRefName
                        reviews(first: 20) {
                            nodes {
                                state
                                author {
                                    login
                                }
                                submittedAt
                                commit {
                                    oid
                                }
                            }
                        }
                        commits(first: 50) {
                            nodes {
                                commit {
                                    oid
                                    committedDate
                                    pushedDate
                                    author {
                                        user {
                                            login
                                        }
                                        name
                                        email
                                    }
                                    message
                                }
                            }
                        }
                    }
                }
            }
            ";
            }

            $query = '
        query {
            ' . implode("\n", $repoQueries) . '
        }
        ';

            $response = $this->executeGraphQLQuery($user, $query);

            if (!$response->successful()) {
                continue;
            }

            $data = $response->json();
            if (isset($data['errors'])) {
                \Log::error('GraphQL yesterday activity query errors', ['errors' => $data['errors']]);
                continue;
            }

            foreach ($chunk as $index => $repo) {
                $repoKey = "repo{$index}";
                $repoData = $data['data'][$repoKey] ?? null;

                if (!$repoData || !isset($repoData['pullRequests']['nodes'])) {
                    continue;
                }

                $this->processRepositoryActivity(
                    $allActivity,
                    $repoData['pullRequests']['nodes'],
                    $repo,
                    $user,
                    $startDate,
                    $endDate
                );
            }
        }

        return $allActivity;
    }

    private function processRepositoryActivity(array &$activity, array $pullRequests, array $repo, User $user, Carbon $startDate, Carbon $endDate): void
    {
        foreach ($pullRequests as $pr) {
            $prAuthor = $pr['author']['login'] ?? null;
            $repoFullName = "{$repo['owner']}/{$repo['name']}";

            if ($prAuthor === $user->github_username && $pr['createdAt']) {
                $createdAt = Carbon::parse($pr['createdAt']);
                if ($createdAt->between($startDate, $endDate)) {
                    $activity['opened_prs'][] = array_merge($pr, [
                        'repository_name' => $repo['name'],
                        'repository_owner' => $repo['owner'],
                        'repository_full_name' => $repoFullName
                    ]);
                }
            }

            if ($prAuthor === $user->github_username && $pr['mergedAt']) {
                $mergedAt = Carbon::parse($pr['mergedAt']);
                if ($mergedAt->between($startDate, $endDate)) {
                    $activity['merged_prs'][] = array_merge($pr, [
                        'repository_name' => $repo['name'],
                        'repository_owner' => $repo['owner'],
                        'repository_full_name' => $repoFullName
                    ]);
                }
            }

            if ($pr['state'] === 'OPEN') {
                if ($pr['isDraft'] && $prAuthor === $user->github_username) {
                    $activity['draft_prs'][] = array_merge($pr, [
                        'repository_name' => $repo['name'],
                        'repository_owner' => $repo['owner'],
                        'repository_full_name' => $repoFullName
                    ]);
                } elseif (!$pr['isDraft'] && $prAuthor === $user->github_username) {
                    $activity['prs_ready_for_review'][] = array_merge($pr, [
                        'repository_name' => $repo['name'],
                        'repository_owner' => $repo['owner'],
                        'repository_full_name' => $repoFullName
                    ]);
                }
            }

            foreach ($pr['reviews']['nodes'] ?? [] as $review) {
                $reviewAuthor = $review['author']['login'] ?? null;
                if ($reviewAuthor === $user->github_username && $review['submittedAt']) {
                    $submittedAt = Carbon::parse($review['submittedAt']);
                    if ($submittedAt->between($startDate, $endDate)) {
                        $reviewData = array_merge($review, [
                            'pr_number' => $pr['number'],
                            'pr_title' => $pr['title'],
                            'repository_name' => $repo['name'],
                            'repository_owner' => $repo['owner'],
                            'repository_full_name' => $repoFullName
                        ]);

                        $activity['reviews_completed'][] = $reviewData;

                        switch ($review['state']) {
                            case 'APPROVED':
                                $activity['reviews_approved']++;
                                break;
                            case 'CHANGES_REQUESTED':
                                $activity['reviews_changes_requested']++;
                                break;
                            case 'COMMENTED':
                                $activity['reviews_commented']++;
                                break;
                            case 'DISMISSED':
                                $activity['reviews_dismissed']++;
                                break;
                        }
                    }
                }
            }

            foreach ($pr['commits']['nodes'] ?? [] as $commitNode) {
                $commit = $commitNode['commit'];
                $commitAuthor = $commit['author']['user']['login'] ?? null;

                if ($commitAuthor === $user->github_username) {
                    $commitDate = Carbon::parse($commit['pushedDate'] ?? $commit['committedDate']);
                    if ($commitDate->between($startDate, $endDate)) {
                        $activity['total_commits']++;
                        $activity['commits'][] = array_merge($commit, [
                            'pr_number' => $pr['number'],
                            'pr_title' => $pr['title'],
                            'repository_name' => $repo['name'],
                            'repository_owner' => $repo['owner'],
                            'repository_full_name' => $repoFullName
                        ]);
                    }
                }
            }
        }
    }

    private function getEmptyActivityData(): array
    {
        return [
            'opened_prs' => [],
            'merged_prs' => [],
            'draft_prs' => [],
            'prs_ready_for_review' => [],
            'reviews_completed' => [],
            'reviews_approved' => 0,
            'reviews_commented' => 0,
            'reviews_changes_requested' => 0,
            'reviews_dismissed' => 0,
            'review_comments_count' => 0,
            'commits' => [],
            'total_commits' => 0
        ];
    }

    public function getPullRequestBuildStatus(User $user, string $repoOwner, string $repoName, int $prNumber): array
    {
        $query = '
        query($owner: String!, $repo: String!, $number: Int!) {
            repository(owner: $owner, name: $repo) {
                pullRequest(number: $number) {
                    number
                    state
                    headRefOid
                    commits(last: 1) {
                        nodes {
                            commit {
                                oid
                                status {
                                    state
                                    contexts {
                                        context
                                        state
                                        targetUrl
                                        description
                                    }
                                }
                                checkSuites(first: 10) {
                                    nodes {
                                        status
                                        conclusion
                                        checkRuns(first: 50) {
                                            nodes {
                                                name
                                                status
                                                conclusion
                                                detailsUrl
                                                summary
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    ';

        $variables = [
            'owner' => $repoOwner,
            'repo' => $repoName,
            'number' => $prNumber
        ];

        $response = $this->executeGraphQLQuery($user, $query, $variables);

        if (!$response->successful()) {
            \Log::error('Failed to fetch PR build status', [
                'repo' => "{$repoOwner}/{$repoName}",
                'pr_number' => $prNumber,
                'response' => $response->body()
            ]);
            return $this->getEmptyBuildStatus($repoOwner, $repoName);
        }

        $data = $response->json();
        $pr = $data['data']['repository']['pullRequest'] ?? null;

        if (!$pr) {
            return $this->getEmptyBuildStatus($repoOwner, $repoName);
        }

        return $this->processPRBuildStatus($pr, $repoOwner, $repoName);
    }

    private function processPRBuildStatus(array $pr, string $repoOwner, string $repoName): array
    {
        $repoFullName = "{$repoOwner}/{$repoName}";
        $latestCommit = $pr['commits']['nodes'][0]['commit'] ?? null;

        $buildStatus = [
            'sha' => $pr['headRefOid'],
            'overall_state' => 'unknown',
            'statuses' => [],
            'check_runs' => [],
            'total_count' => 0,
            'repository_owner' => $repoOwner,
            'repository_name' => $repoName,
            'repository_full_name' => $repoFullName,
        ];

        if (strtolower($pr['state']) === 'open' && $latestCommit) {
            $legacyStatus = $latestCommit['status'] ?? null;
            $checkSuites = $latestCommit['checkSuites']['nodes'] ?? [];

            $legacyState = $legacyStatus['state'] ?? 'unknown';
            $legacyContexts = $legacyStatus['contexts'] ?? [];

            // Normalize legacy status contexts for frontend
            $normalizedLegacyContexts = array_map(function ($context) {
                return [
                    'context' => $context['context'] ?? '',
                    'state' => $context['state'] ?? '',
                    'target_url' => $context['targetUrl'] ?? $context['target_url'] ?? '',
                    'description' => $context['description'] ?? '',
                ];
            }, $legacyContexts);

            $allCheckRuns = [];
            foreach ($checkSuites as $suite) {
                $checkRuns = $suite['checkRuns']['nodes'] ?? [];
                // Map details_url/detailsUrl and ensure all keys are present for frontend compatibility
                foreach ($checkRuns as &$run) {
                    if (isset($run['details_url'])) {
                        $run['detailsUrl'] = $run['details_url'];
                    } elseif (isset($run['detailsUrl'])) {
                        $run['detailsUrl'] = $run['detailsUrl'];
                    }
                    // Ensure all expected keys exist
                    $run['name'] = $run['name'] ?? '';
                    $run['status'] = $run['status'] ?? '';
                    $run['conclusion'] = $run['conclusion'] ?? '';
                    $run['summary'] = $run['summary'] ?? '';
                }
                unset($run);
                $allCheckRuns = array_merge($allCheckRuns, $checkRuns);
            }

            $checkRunsState = $this->determineOverallCheckState($allCheckRuns);
            $overallState = $this->combineStates($legacyState, $checkRunsState);

            $buildStatus = array_merge($buildStatus, [
                'overall_state' => strtolower($overallState),
                'statuses' => $normalizedLegacyContexts,
                'check_runs' => $allCheckRuns,
                'total_count' => count($normalizedLegacyContexts) + count($allCheckRuns),
            ]);
        }

        return $buildStatus;
    }

    private function getEmptyBuildStatus(string $repoOwner, string $repoName): array
    {
        return [
            'sha' => '',
            'overall_state' => 'unknown',
            'statuses' => [],
            'check_runs' => [],
            'total_count' => 0,
            'repository_owner' => $repoOwner,
            'repository_name' => $repoName,
            'repository_full_name' => "{$repoOwner}/{$repoName}",
        ];
    }
}
