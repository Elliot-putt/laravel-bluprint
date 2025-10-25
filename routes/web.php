<?php

use App\Http\Controllers\Auth\HandleAccountController;
use App\Http\Controllers\Auth\HandleGitHubAccountRedirectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PullRequestsController;
use App\Http\Controllers\StandupController;
use App\Http\Controllers\SubmitPRController;
use App\Http\Controllers\SubmittedPRViewController;
use App\Http\Controllers\TemplateController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/api/dashboard/load-more-branches', [DashboardController::class, 'loadMoreBranches'])->middleware(['auth', 'verified'])->name('dashboard.load-more-branches');
Route::post('/api/dashboard/load-more-repositories', [DashboardController::class, 'loadMoreRepositories'])->middleware(['auth', 'verified'])->name('dashboard.load-more-repositories');
Route::get('/pull-requests', [PullRequestsController::class , 'index'])->middleware(['auth', 'verified'])->name('pull-requests.index');
Route::get('/api/pull-requests/{owner}/{repo}/{number}/build-status', [PullRequestsController::class, 'buildStatus'])->middleware(['auth', 'verified'])->name('pull-requests.build-status');
Route::get('/standup', [StandupController::class , 'index'])->middleware(['auth', 'verified'])->name('standup.index');
Route::post('/submit/pr', SubmitPRController::class)->middleware(['auth', 'verified'])->name('submit.pr');
Route::get('/submitted-pr', SubmittedPRViewController::class)->middleware(['auth', 'verified'])->name('submitted.pr.view');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Template routes
    Route::resource('templates', TemplateController::class);
    Route::post('/templates/{template}/set-default', [TemplateController::class, 'setDefault'])->name('templates.set-default');

    // Media routes
    Route::get('/api/media', [App\Http\Controllers\MediaController::class, 'index'])->name('media.index');
    Route::post('/api/media', [App\Http\Controllers\MediaController::class, 'store'])->name('media.store');
    Route::delete('/api/media/{media}', [App\Http\Controllers\MediaController::class, 'destroy'])->name('media.destroy');
});

Route::middleware('web')->group(function () {
    Route::get('account/callback', HandleAccountController::class)->name('account.callback');
    Route::get('account/redirect', HandleGitHubAccountRedirectController::class)->name('account.redirect');
});

require __DIR__.'/auth.php';
