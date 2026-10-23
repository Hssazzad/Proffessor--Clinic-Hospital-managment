<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Surface overdue invoices (due > 0, payment date before today) as
// in-app reminders on an hourly cadence.
Schedule::command('billing:notify-overdue')->hourly();
