<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Services\DatabaseBackupProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class PerformDatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perform:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a database backup.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupProvider $backupService): void
    {
        $filename = 'backup-'.now()->timestamp.'.sql';

        $sourcePath = database_path('database.sqlite');
        $backupPath = database_path('backups/'.$filename);

        $backupService->performBackup($sourcePath, $backupPath);

        $this->cleanupOldBackups();
    }

    private function cleanupOldBackups(): void
    {
        $glob = type(File::glob(database_path('backups/*.sql')))->asArray();

        collect($glob)->sort()->reverse()->slice(20)->each(
            fn (string $backup): bool => File::delete($backup),
        );
    }
}
