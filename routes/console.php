<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment('Build secure, supervised visitor flows.');
})->purpose('Display an inspirational quote');

Schedule::command('vms:prune-visitor-pii')->dailyAt('02:15');
