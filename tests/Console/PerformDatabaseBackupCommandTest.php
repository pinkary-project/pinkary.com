<?php

declare(strict_types=1);

use App\Console\Commands\PerformDatabaseBackupCommand;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

test('perform database backup', function () {
    Process::fake();

    File::shouldReceive('glob')
        ->once();

    $this->artisan(PerformDatabaseBackupCommand::class)
        ->assertExitCode(0);

    Process::assertRan(function (PendingProcess $process) {
        $command = $process->command;

        return $command[0] === 'sqlite3' &&
            $command[1] === database_path('database.sqlite') &&
            str_starts_with($command[2], '.backup '.database_path('backups/backup-'));
    });

});
