<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('fetch:news-api:articles')->hourly();
Schedule::command('fetch:guardian-news')->everyMinute();
Schedule::command('fetch:new-york-times:articles')->everyMinute();
