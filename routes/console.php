<?php

declare(strict_types=1);

use App\Models\User;
use App\Console\Commands\DeleteNonEmailVerifiedUsersCommand;
use App\Console\Commands\PerformDatabaseBackupCommand;
use App\Console\Commands\SendDailyEmailsCommand;
use App\Console\Commands\SendWeeklyEmailsCommand;
use App\Console\Commands\SyncVerifiedUsersCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendDailyEmailsCommand::class)->dailyAt('13:00');
Schedule::command(SendWeeklyEmailsCommand::class)->weeklyOn(1, '13:00');
Schedule::command(PerformDatabaseBackupCommand::class)->hourly();
Schedule::command(DeleteNonEmailVerifiedUsersCommand::class)->hourly();
Schedule::command(SyncVerifiedUsersCommand::class)->daily();

Artisan::command('nuno', function () {
    // Ensure nuno follows all users.

    $nuno = User::where('email', 'enunomaduro@gmail.com')->first();

    foreach (User::all() as $user) {
        if ($user->id === $nuno->id) {
            continue;
        }

        if ($user->followers()->where('follower_id', $nuno->id)->doesntExist()) {
            $user->followers()->attach($nuno->id);
        }
    }
})->purpose('Ensure nuno follows all users.');
