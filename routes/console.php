<?php

use App\Models\UserLocation;
use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:dispatch')->everyTwoMinutes()->withoutOverlapping(120);
Schedule::call(fn () => UserLocation::where('updated_at', '<', now()->subDay())->delete())->daily()->name('locations:prune')->withoutOverlapping();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
