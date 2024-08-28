<?php

declare(strict_types=1);

use App\Console\Commands\PerformDatabaseBackupCommand;
use App\Contracts\Services\DatabaseBackupProvider;
use Illuminate\Support\Facades\File;

test('perform database backup', function () {
    $backupService = Mockery::mock(DatabaseBackupProvider::class);

    $this->app->instance(DatabaseBackupProvider::class, $backupService);

    $backupService->shouldReceive('performBackup')
        ->once();

    File::shouldReceive('glob')
        ->once()
        ->with(database_path('backups/*.sql'))
        ->andReturn([
            database_path('backups/backup-1.sql'),
            database_path('backups/backup-2.sql'),
            database_path('backups/backup-3.sql'),
            database_path('backups/backup-4.sql'),
            database_path('backups/backup-5.sql'),
            database_path('backups/backup-6.sql'),
            database_path('backups/backup-7.sql'),
            database_path('backups/backup-8.sql'),
            database_path('backups/backup-9.sql'),
            database_path('backups/backup-10.sql'),
            database_path('backups/backup-11.sql'),
            database_path('backups/backup-12.sql'),
            database_path('backups/backup-13.sql'),
            database_path('backups/backup-14.sql'),
            database_path('backups/backup-15.sql'),
            database_path('backups/backup-16.sql'),
            database_path('backups/backup-17.sql'),
            database_path('backups/backup-18.sql'),
            database_path('backups/backup-19.sql'),
            database_path('backups/backup-20.sql'),
            database_path('backups/backup-21.sql'),
        ]);

    File::shouldReceive('delete')
        ->times(1)
        ->with(database_path('backups/backup-1.sql'))
        ->andReturnTrue();

    $this->artisan(PerformDatabaseBackupCommand::class)
        ->assertExitCode(0);
});
