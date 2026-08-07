<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily upkeep: mark overdue invoices. Runs at 01:00 every day.
Schedule::command('invoices:mark-overdue')->dailyAt('01:00');