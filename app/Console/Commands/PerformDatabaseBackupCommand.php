<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;

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
    public function handle(): void
    {
        $filename = 'backup-' . now()->timestamp . '.sql';

        $result = Process::run([
            "sqlite3",
            database_path("database.sqlite"),
            sprintf(".backup %s", database_path('backups/' . $filename))
        ]);

        if (! $result->successful()) {
            $this->error("Database backup failed: " . $result->errorOutput());
            error("Database backup failed: " . $result->errorOutput());
            return;
        }

        $glob = File::glob(database_path('backups/*.sql'));

        collect($glob)->sort()->reverse()->slice(4)->each(
            fn(string $backup): bool => File::delete($backup),
        );
    }
}
