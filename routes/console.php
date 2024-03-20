<?php

declare(strict_types=1);

use App\Console\Commands\DeleteNonVerifiedUsersCommand;
use App\Console\Commands\PerformDatabaseBackupCommand;
use App\Console\Commands\SendDailyEmailsCommand;
use App\Console\Commands\SendWeeklyEmailsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendDailyEmailsCommand::class)->dailyAt('13:00');
Schedule::command(SendWeeklyEmailsCommand::class)->weeklyOn(1, '13:00');
Schedule::command(PerformDatabaseBackupCommand::class)->hourly();
Schedule::command(DeleteNonVerifiedUsersCommand::class)->hourly();
