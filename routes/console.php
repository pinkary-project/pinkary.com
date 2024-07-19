<?php

declare(strict_types=1);

use App\Console\Commands\DeleteNonEmailVerifiedUsersCommand;
use App\Console\Commands\PerformDatabaseBackupCommand;
use App\Console\Commands\SendDailyEmailsCommand;
use App\Console\Commands\SendWeeklyEmailsCommand;
use App\Console\Commands\SyncVerifiedUsersCommand;
use App\Jobs\CleanUnusedUploadedImages;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendDailyEmailsCommand::class)->dailyAt('13:00');
Schedule::command(SendWeeklyEmailsCommand::class)->weeklyOn(1, '13:00');
Schedule::command(PerformDatabaseBackupCommand::class)->hourly();
Schedule::command(DeleteNonEmailVerifiedUsersCommand::class)->hourly();
Schedule::command(SyncVerifiedUsersCommand::class)->daily();
Schedule::job(CleanUnusedUploadedImages::class)->hourly();
