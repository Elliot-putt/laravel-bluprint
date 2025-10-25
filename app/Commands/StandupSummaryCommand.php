<?php

namespace App\Commands;

use App\Jobs\StandupSummaryJob;
use App\Models\User;
use Illuminate\Console\Command;

class StandupSummaryCommand extends Command
{
    protected $signature = 'standup:summary {--user=* : Specific user IDs to process}';
    protected $description = 'Uses AI to generate a standup summary based on the users yesterdays activity from GitHub and JIRA.';

    public function handle(): void
    {
        $userIds = $this->option('user');

        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)->get();
            $this->info('Processing ' . $users->count() . ' specific users...');
        } else {
            $users = User::whereNotNull('github_access_token')->get();
            $this->info('Processing ' . $users->count() . ' users with GitHub or JIRA integration...');
        }

        $delay = 0;

        $users->each(function ($user) use (&$delay) {
            $this->info("Queuing standup summary for user: {$user->name} (ID: {$user->id}) - Delay: {$delay}s");

            StandupSummaryJob::dispatch($user)->delay(now()->addSeconds($delay));

            $delay += 10;
        });

        $this->info('All standup summary jobs have been queued with 10-second delays.');
    }
}
