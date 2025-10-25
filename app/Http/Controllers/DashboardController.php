<?php

namespace App\Http\Controllers;

use App\Http\Requests\PullRequest;
use App\Models\User;
use App\Services\AIService;
use App\Services\GitHubService;
use App\Services\JiraService;
use App\Services\TemplateService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(protected readonly AIService $AIService, protected readonly GitHubService $gitHubService, protected readonly JiraService $jiraService, protected readonly TemplateService $templateService)
    {

    }
    public function __invoke(PullRequest $request)
    {
        $user = auth()->user();
        $repoData = $request->all();

        $this->checkSystemStatus($user);

        $differenceInCodeOutput = $this->gitHubService->getBranchCodeDifference($user, $repoData);
        $isCurrentlyGitHubPR = $this->gitHubService->isCurrentlyGitHubPR($user, $repoData);
        $jirContext = $this->jiraService->jiraContext($user, $request->get('ticket_key'));

        $templateId = $request->get('template_id') ?: $user->default_template_id;

        $prContent = $differenceInCodeOutput
            ? $this->AIService->generatePR($user, $differenceInCodeOutput, $jirContext, $templateId)
            : [];

        return Inertia::render('Dashboard', [
            'serviceUnavailable' => false,
            'jiraTickets' => Inertia::defer(fn () => $this->jiraService->getJiraTicketsInProgress($user)),
            'recentlyMadeBranches' => Inertia::defer(fn () => $this->gitHubService->getRecentlyMadeBranches($user)),
            'isCurrentlyGitHubPR' => $isCurrentlyGitHubPR,
            'branches' => $this->gitHubService->getBranches($user, $repoData),
            'repositories' => $this->gitHubService->getRepositories($user),
            'labels' => $this->gitHubService->getLabels($user, $repoData),
            'reviewers' => $this->gitHubService->getReviewers($user, $repoData),
            'templates' => $this->templateService->getUserTemplates($user),
            'defaultTemplateId' => $user->default_template_id,
            'title' => array_get($prContent, 'title', ''),
            'body' => array_get($prContent, 'body', ''),
        ]);
    }

    public function loadMoreBranches(PullRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $data = $request->all();

        $result = $this->gitHubService->getBranchesPaginated($user, $data);

        return response()->json([
            'branches' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    public function loadMoreRepositories(PullRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $data = $request->all();

        $result = $this->gitHubService->getRepositoriesPaginated($user, $data);

        return response()->json([
            'repositories' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    private function checkSystemStatus(User $user)
    {
        if ($this->AIService->isAvailable() === false) {
            return Inertia::render('Dashboard', [
                'serviceUnavailable' => true,
                'templates' => $user->templates,
                'defaultTemplateId' => $user->default_template_id,
            ]);
        }
    }

}
