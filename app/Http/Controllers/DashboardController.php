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
    public function __invoke()
    {
        return Inertia::render('Dashboard');
    }
}
