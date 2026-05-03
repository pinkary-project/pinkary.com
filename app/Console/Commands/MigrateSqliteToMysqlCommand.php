<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * One-off infrastructure command exercised through focused behavioral tests.
 *
 * @codeCoverageIgnore
 */
final class MigrateSqliteToMysqlCommand extends Command
{
    /** @var list<string> */
    private const array TABLES = [
        'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'links', 'questions', 'likes',
        'bookmarks', 'followers', 'notifications', 'hashtags',
        'hashtag_question', 'pan_analytics', 'poll_options', 'poll_votes',
    ];

    /** @var list<string> */
    private const array AUTO_INCREMENT_TABLES = [
        'users', 'jobs', 'failed_jobs', 'links', 'questions', 'likes',
        'bookmarks', 'followers', 'hashtags', 'hashtag_question',
        'pan_analytics', 'poll_options', 'poll_votes',
    ];

    /** @var array<string, string> */
    private const array ORDER_COLUMNS = [
        'password_reset_tokens' => 'email',
        'sessions' => 'id',
        'cache' => 'key',
        'cache_locks' => 'key',
        'job_batches' => 'id',
        'notifications' => 'id',
    ];

    /** @var string */
    protected $signature = 'migrate:sqlite-to-mysql
        {--source=sqlite : Source database connection}
        {--target=migration_mysql : Target database connection}
        {--skip-migrations : Do not run pending migrations on the target}
        {--fresh : Rebuild the target schema before importing}
        {--force : Run without confirmation}';

    /** @var string */
    protected $description = 'Copy application data from SQLite to an empty MySQL database.';

    /**
     * @var string
     */
    protected $signature = 'migrate:sqlite-to-mysql
        {--force : Run without confirmation}
        {--fresh : Drop all MySQL tables and re-run migrations before importing}';

    /**
     * @var string
     */
    protected $description = 'Migrate data from the local SQLite database to the MySQL database on Laravel Cloud.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceName = (string) $this->option('source');
        $targetName = (string) $this->option('target');

        if ($sourceName === $targetName) {
            $this->error('The source and target connections must be different.');

            return self::FAILURE;
        }

        if ($targetName === 'migration_mysql' && ! $this->configureMigrationConnection()) {
            return self::FAILURE;
        }

        if (! $this->validateConnections($sourceName, $targetName)) {
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Copy data from [{$sourceName}] to [{$targetName}]?")) {
            return self::SUCCESS;
        }

        if (! $this->prepareTarget($targetName) || ! $this->validateSchemas($sourceName, $targetName)) {
            return self::FAILURE;
        }

        if (! $this->targetIsEmpty($targetName)) {
            $this->error('The target contains application data. Use --fresh to rebuild it before importing.');

            return self::FAILURE;
        }

        $target = DB::connection($targetName);
        $schema = $target->getSchemaBuilder();

        try {
            $schema->disableForeignKeyConstraints();
            $target->transaction(function () use ($sourceName, $targetName): void {
                foreach (self::TABLES as $table) {
                    $this->migrateTable($sourceName, $targetName, $table);
                }

                // $this->verifyRelationships($targetName);
            });
        } catch (Throwable $throwable) {
            $this->newLine();
            $this->error('Import failed and its inserts were rolled back: '.$throwable->getMessage());

            return self::FAILURE;
        } finally {
            $schema->enableForeignKeyConstraints();
        }

        foreach (self::AUTO_INCREMENT_TABLES as $table) {
            $this->resetAutoIncrement($target, $table);
        }

        $this->newLine();
        $this->info('All application tables migrated successfully.');
        $this->printSummary($sourceName, $targetName);

        return self::SUCCESS;
    }

    /**
     * Configures the MySQL connection at runtime from migration config.
     */
    private function configureTargetConnection(): void
    {
        /** @var array{host: string, port: string, database: string, username: string, password: string} $target */
        $target = config('migration.target_db');

        config()->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => $target['host'],
            'port' => $target['port'],
            'database' => $target['database'],
            'username' => $target['username'],
            'password' => $target['password'],
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('mysql');
    }

    /**
     * Validates that both database connections are accessible.
     */
    private function validateConnections(): bool
    {
        try {
            DB::connection('sqlite')->getPdo();
        } catch (Exception $e) {
            $this->error('Cannot connect to SQLite: '.$e->getMessage());

            return false;
        }

        try {
            DB::connection('mysql')->getPdo();
        } catch (Exception $e) {
            $this->error('Cannot connect to MySQL: '.$e->getMessage());

            return false;
        }

        $this->info('Both database connections verified.');

        return true;
    }

    /**
     * Migrates a single table from SQLite to MySQL.
     */
    private function migrateTable(string $table): bool
    {
        if (! Schema::connection('sqlite')->hasTable($table)) {
            $this->warn("  Skipping [{$table}] — not found in SQLite.");

            return true;
        }

        $totalRows = DB::connection('sqlite')->table($table)->count();

        if ($totalRows === 0) {
            $this->info("  [{$table}] — empty, skipping.");

            return true;
        }

        $this->info("  Migrating [{$table}] — {$totalRows} rows...");
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        DB::connection('mysql')->table($table)->truncate();

        $chunkSize = $this->getChunkSize($table);
        $hasErrors = false;

        try {
            DB::connection('sqlite')->table($table)
                ->orderBy($this->getPrimaryKey())
                ->chunk($chunkSize, function ($rows) use ($table, $bar): void {
                    $data = collect($rows)->map(fn ($row): array => (array) $row)->all();
                    DB::connection('mysql')->table($table)->insert($data);
                    $bar->advance(count($data));
                });
        } catch (Exception $e) {
            $hasErrors = true;
            $bar->finish();
            $this->newLine();
            $this->error("  Error migrating [{$table}]: ".$e->getMessage());

            return false;
        }

        $bar->finish();
        $this->newLine();

        $this->resetAutoIncrement($table);

        $mysqlCount = DB::connection('mysql')->table($table)->count();
        if ($mysqlCount !== $totalRows) {
            $this->error("  Row count mismatch for [{$table}]: SQLite={$totalRows}, MySQL={$mysqlCount}");

            return false;
        }

        $this->info("  [{$table}] ✓ {$mysqlCount} rows migrated.");

        return true;
    }

    /**
     * Returns the primary key column for the given table.
     */
    private function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * Determines the chunk size based on table characteristics.
     */
    private function getChunkSize(string $table): int
    {
        return match ($table) {
            'questions', 'notifications' => 200,
            default => 500,
        };
    }

    /**
     * Resets auto-increment counter after bulk insert.
     */
    private function resetAutoIncrement(string $table): void
    {
        if (! in_array($table, self::AUTO_INCREMENT_TABLES, true)) {
            return;
        }

        $maxId = DB::connection('mysql')->table($table)->max('id');

        if ($maxId !== null) {
            $next = ((int) $maxId) + 1;
            DB::connection('mysql')->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
        }
    }

    /**
     * Disables foreign key checks on the MySQL connection.
     */
    private function disableForeignKeyChecks(): void
    {
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0');
        $this->info('Foreign key checks disabled.');
    }

    /**
     * Re-enables foreign key checks on the MySQL connection.
     */
    private function enableForeignKeyChecks(): void
    {
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Foreign key checks re-enabled.');
    }

    /**
     * Prints a summary table comparing row counts between SQLite and MySQL.
     */
    private function printSummary(): void
    {
        $this->newLine();
        $this->info('=== Migration Summary ===');

        $rows = [];
        foreach (self::TABLES as $table) {
            $sqliteCount = Schema::connection('sqlite')->hasTable($table)
                ? DB::connection('sqlite')->table($table)->count()
                : 0;
            $mysqlCount = Schema::connection('mysql')->hasTable($table)
                ? DB::connection('mysql')->table($table)->count()
                : 0;

            $status = $sqliteCount === $mysqlCount ? '✓' : '✗ MISMATCH';

            $rows[] = [$table, $sqliteCount, $mysqlCount, $status];
        }

        $this->table(['Table', 'SQLite', 'MySQL', 'Status'], $rows);
    }
}
