<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('standup:summary', function () {
    $this->call('App\Commands\StandupSummaryCommand');
})->purpose('Generate a standup summary for the day');
