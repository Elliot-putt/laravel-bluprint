<?php

namespace App\Http\Controllers;

use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitPRController extends Controller
{
    public function __invoke(Request $request, GitHubService $service)
    {
        $result = $service->submitPR(Auth::user(), [
            'repository_owner' => $request->get('repository_owner'),
            'isCurrentlyGitHubPR' => $request->get('isCurrentlyGitHubPR'),
            'repository' => $request->get('repository'),
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'head' => $request->get('head'),
            'base' => $request->get('base'),
            'labels' => $request->get('labels'),
            'reviewers' => $request->get('reviewers'),
            'isDraft' => $request->get('isDraft', false),
            'jira_ticket_key' => $request->get('ticket_key'),
        ]);

        return redirect()->route('submitted.pr.view', [
            'prLink' => $result['pr_url'],
            'repositoryName' => $request->get('repository'),
            'jiraTransition' => $result['jira_transition'],
        ]);
    }

}
