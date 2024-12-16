<?php

declare(strict_types=1);

use App\Console\Commands\PerformDatabaseBackupCommand;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

test('perform database backup', function () {
    Process::fake();

    File::shouldReceive('glob')
        ->once()
        ->with(database_path('backups/*.sql'))
        ->andReturn([
            database_path('backups/backup-1.sql'),
            database_path('backups/backup-2.sql'),
            database_path('backups/backup-3.sql'),
            database_path('backups/backup-4.sql'),
            database_path('backups/backup-5.sql'),
        ]);

    File::shouldReceive('delete')
        ->times(1)
        ->with(database_path('backups/backup-1.sql'))
        ->andReturnTrue();

    $this->artisan(PerformDatabaseBackupCommand::class)
        ->assertExitCode(0);

    Process::assertRan(function (PendingProcess $process) {
        $command = $process->command;

        return $command[0] === 'sqlite3' &&
            $command[1] === database_path('database.sqlite') &&
            str_starts_with($command[2], '.backup ' . database_path('backups/backup-'));
    });
});
