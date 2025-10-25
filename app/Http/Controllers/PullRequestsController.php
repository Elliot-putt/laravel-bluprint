<?php

namespace App\Http\Controllers;

use App\Data\RepositoryData;
use App\Http\Requests\PullRequestsIndexRequest;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class PullRequestsController extends Controller
{
    /**
     * Display a listing of pull requests.
     *
     * @param  PullRequestsIndexRequest  $request
     * @param  GitHubService  $service
     * @return \Inertia\Response
     *
     * The 'requests' prop is a LengthAwarePaginator where each item is an array:
     *   [
     *     'pull_request' => array, // PR data (see GitHubService::processGraphQLPRData)
     *     'build_status' => array, // Build status info
     *     'review_data' => array,  // Review data info
     *   ]
     *
     * The 'repositories' prop is a Collection of RepositoryData.
     */
    public function index(PullRequestsIndexRequest $request, GitHubService $service): \Inertia\Response
    {
        $user = auth()->user();
        $repoOwner = $request->get('repository_owner');
        $repoName = $request->get('repository');
        $state = $request->get('state');
        $showAll = $request->boolean('show_all', false);
        $perPage = $request->integer('per_page', 25);
        $page = $request->integer('page', 1);
        $repoId = null;

        if (!$repoOwner || !$repoName) {
            $activeRepos = $service->getActiveRepositories($user);
            $repoName = $activeRepos ? $activeRepos[0]['name'] : null;
            $repoOwner = $activeRepos ? $activeRepos[0]['owner'] : null;
            $repoId = $activeRepos ? $activeRepos[0]['id'] : null;
        } else {
            // Find the repository ID from the service when we have specific repo params
            $repositories = $service->getRepositories($user);
            $repository = $repositories->first(function ($repo) use ($repoOwner, $repoName) {
                return $repo->owner === $repoOwner && $repo->name === $repoName;
            });
            $repoId = $repository?->id;
        }

        return Inertia::render('Pulls', [
            'requests' => Inertia::defer(fn () => $service->getAllPullRequestData(
                $user,
                $repoOwner,
                $repoName,
                $state ?? 'open',
                $showAll,
                $perPage,
                $page
            )),
            'repositoryId' => $repoId,
            'repositories' => Inertia::defer(fn () => $service->getRepositories($user)),
            'filters' => [
                'repository_owner' => $repoOwner,
                'repository' => $repoName,
                'state' => $state ?? 'open',
                'show_all' => $showAll,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Get build status for a specific pull request.
     *
     * @param  GitHubService  $service
     * @param  string  $owner
     * @param  string  $repo
     * @param  int  $number
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildStatus(GitHubService $service, string $owner, string $repo, int $number): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $buildStatus = $service->getPullRequestBuildStatus($user, $owner, $repo, $number);

        return response()->json([
            'build_status' => $buildStatus
        ]);
    }
}
