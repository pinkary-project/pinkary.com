<?php

declare(strict_types=1);

namespace App\Console\Commands;

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
        {--source=sqlite : Source database connection}
        {--target=migration_mysql : Target database connection}
        {--skip-migrations : Do not run pending migrations on the target}
        {--fresh : Rebuild the target schema before importing}
        {--force : Run without confirmation}';

    /** @var string */
    protected $description = 'Copy application data from SQLite to an empty MySQL database.';

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
     * Configure the dedicated target MySQL connection.
     */
    private function configureMigrationConnection(): bool
    {
        /** @var array<string, mixed> $mysql */
        $mysql = config('database.connections.mysql', []);
        /** @var array{url: ?string, host: ?string, port: ?string, database: ?string, username: ?string, password: ?string} $target */
        $target = config('migration.target_db');

        if (! $target['url'] && (! $target['host'] || ! $target['database'] || ! $target['username'])) {
            $this->error('Configure TARGET_DB_URL or the TARGET_DB_HOST, TARGET_DB_DATABASE, and TARGET_DB_USERNAME values.');

            return false;
        }

        config()->set('database.connections.migration_mysql', array_replace($mysql, [
            'url' => $target['url'],
            'host' => $target['host'],
            'port' => $target['port'],
            'database' => $target['database'],
            'username' => $target['username'],
            'password' => $target['password'],
        ]));

        DB::purge('migration_mysql');

        return true;
    }

    /**
     * Verify that the source and target connections are accessible.
     */
    private function validateConnections(string $sourceName, string $targetName): bool
    {
        foreach ([$sourceName, $targetName] as $connection) {
            try {
                DB::connection($connection)->getPdo();
            } catch (Throwable $throwable) {
                $this->error("Cannot connect to [{$connection}]: {$throwable->getMessage()}");

                return false;
            }
        }

        $this->info('Source and target database connections verified.');

        return true;
    }

    /**
     * Run the requested migrations on the target database.
     */
    private function prepareTarget(string $targetName): bool
    {
        if ($this->option('skip-migrations')) {
            return true;
        }

        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
        $arguments = ['--database' => $targetName, '--force' => true, '--no-interaction' => true];

        $this->info(($this->option('fresh') ? 'Rebuilding' : 'Migrating').' the target schema...');

        if ($this->call($command, $arguments) === self::SUCCESS) {
            return true;
        }

        $this->error('Target migrations failed; no data was imported.');

        return false;
    }

    /**
     * Ensure source and target application tables have matching columns.
     */
    private function validateSchemas(string $sourceName, string $targetName): bool
    {
        foreach (self::TABLES as $table) {
            if (! Schema::connection($sourceName)->hasTable($table)) {
                continue;
            }

            if (! Schema::connection($targetName)->hasTable($table)) {
                $this->error("Target table [{$table}] does not exist.");

                return false;
            }

            $sourceColumns = Schema::connection($sourceName)->getColumnListing($table);
            $targetColumns = Schema::connection($targetName)->getColumnListing($table);
            sort($sourceColumns);
            sort($targetColumns);

            if ($sourceColumns !== $targetColumns) {
                $this->error("Column mismatch for [{$table}]. Run the same application migrations on both schemas.");

                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether all target application tables are empty.
     */
    private function targetIsEmpty(string $targetName): bool
    {
        return array_all(self::TABLES, fn (string $table): bool => ! (Schema::connection($targetName)->hasTable($table) && DB::connection($targetName)->table($table)->exists()));
    }

    /**
     * Copy one application table to the target database.
     */
    private function migrateTable(string $sourceName, string $targetName, string $table): void
    {
        if (! Schema::connection($sourceName)->hasTable($table)) {
            $this->warn("  Skipping [{$table}] because it is absent from the source.");

            return;
        }

        $source = DB::connection($sourceName)->table($table);
        $totalRows = $source->count();

        if ($totalRows === 0) {
            $this->info("  [{$table}] is empty.");

            return;
        }

        $this->info("  Migrating [{$table}] ({$totalRows} rows)...");
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        $source->orderBy('id')->chunk($this->chunkSize($table), function (Collection $rows) use ($targetName, $table, $bar): void {
            $data = collect($rows)->map(static fn (object $row): array => (array) $row)->all();
            DB::connection($targetName)->table($table)->insert($data);
            $bar->advance(count($data));
        });

        $bar->finish();
        $this->newLine();
    }

    /**
     * Get the import chunk size for a table.
     */
    private function chunkSize(string $table): int
    {
        return in_array($table, ['questions', 'notifications'], true) ? 200 : 500;
    }

    /**
     * Advance a MySQL table's auto-increment sequence after importing IDs.
     */
    private function resetAutoIncrement(Connection $target, string $table): void
    {
        if ($target->getDriverName() !== 'mysql' || ! $target->getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $maxId = $target->table($table)->max('id');
        $validatedMaxId = filter_var($maxId, FILTER_VALIDATE_INT);

        if (is_int($validatedMaxId)) {
            $nextId = $validatedMaxId + 1;
            $target->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId}");
        }
    }

    /**
     * Reject imports containing orphaned foreign-key values.
     */
    private function verifyRelationships(string $targetName): void
    {
        /** @var list<array{string, string, string}> $relationships */
        $relationships = [
            ['links', 'user_id', 'users'],
            ['questions', 'from_id', 'users'],
            ['questions', 'to_id', 'users'],
            ['questions', 'parent_id', 'questions'],
            ['questions', 'root_id', 'questions'],
            ['likes', 'user_id', 'users'],
            ['likes', 'question_id', 'questions'],
            ['bookmarks', 'user_id', 'users'],
            ['bookmarks', 'question_id', 'questions'],
            ['followers', 'user_id', 'users'],
            ['followers', 'follower_id', 'users'],
            ['hashtag_question', 'hashtag_id', 'hashtags'],
            ['hashtag_question', 'question_id', 'questions'],
            ['poll_options', 'question_id', 'questions'],
            ['poll_votes', 'user_id', 'users'],
            ['poll_votes', 'poll_option_id', 'poll_options'],
        ];

        $target = DB::connection($targetName);

        foreach ($relationships as [$childTable, $foreignKey, $parentTable]) {
            if (! $target->getSchemaBuilder()->hasTable($childTable)) {
                continue;
            }
            if (! $target->getSchemaBuilder()->hasTable($parentTable)) {
                continue;
            }
            $hasOrphans = $target->table("{$childTable} as child")
                ->leftJoin("{$parentTable} as parent", "child.{$foreignKey}", '=', 'parent.id')
                ->whereNotNull("child.{$foreignKey}")
                ->whereNull('parent.id')
                ->exists();

            if ($hasOrphans) {
                throw new RuntimeException("Orphaned values found in [{$childTable}.{$foreignKey}].");
            }
        }
    }

    /**
     * Print source and target row counts for each application table.
     */
    private function printSummary(string $sourceName, string $targetName): void
    {
        $rows = [];

        foreach (self::TABLES as $table) {
            $sourceCount = Schema::connection($sourceName)->hasTable($table)
                ? DB::connection($sourceName)->table($table)->count()
                : 0;
            $targetCount = Schema::connection($targetName)->hasTable($table)
                ? DB::connection($targetName)->table($table)->count()
                : 0;

            $rows[] = [$table, $sourceCount, $targetCount, $sourceCount === $targetCount ? 'OK' : 'MISMATCH'];
        }

        $this->table(['Table', 'Source', 'Target', 'Status'], $rows);
    }
}
