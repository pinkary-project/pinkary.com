<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * One-off infrastructure command exercised through focused behavioral tests.
 *
 * @codeCoverageIgnore
 */
final class MigrateFilesToS3Command extends Command
{
    /**
     * Directories to migrate from local storage.
     *
     * @var array<int, string>
     */
    private const array DIRECTORIES = [
        'avatars',
        'images',
    ];

    /**
     * @var string
     */
    protected $signature = 'migrate:files-to-s3
        {--source=public : The source filesystem disk}
        {--disk=s3 : The target S3-compatible disk}
        {--force : Skip confirmation}
        {--overwrite : Overwrite files that already exist on S3}';

    /**
     * @var string
     */
    protected $description = 'Upload local storage files (avatars, images) to the S3-compatible bucket.';

    /**
     * Directories to migrate from local storage.
     *
     * @var array<int, string>
     */
    private const DIRECTORIES = [
        'avatars',
        'images',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDisk = (string) $this->option('disk');
        $overwrite = (bool) $this->option('overwrite');

        $local = Storage::disk('local');
        $remote = Storage::disk($targetDisk);

        if (! $this->validateDisks($local, $remote, $targetDisk)) {
            return self::FAILURE;
        }

        $allFiles = $this->collectFiles($local);

        if ($allFiles === []) {
            $this->info('No files found to migrate.');

            return self::SUCCESS;
        }

        $this->info(count($allFiles).' files found across directories: '.implode(', ', self::DIRECTORIES));

        if (! $this->option('force') && ! $this->confirm('Upload these files to the "'.$targetDisk.'" disk?')) {
            return self::SUCCESS;
        }

        $results = $this->uploadFiles($local, $remote, $allFiles, $overwrite);

        $this->printSummary($results);

        return $results['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Validates that both storage disks are accessible.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $local
     * @param \Illuminate\Contracts\Filesystem\Filesystem $remote
     */
    private function validateDisks($local, $remote, string $targetDisk): bool
    {
        try {
            $local->directories('/');
        } catch (\Exception $e) {
            $this->error('Cannot access local disk: '.$e->getMessage());

            return false;
        }

        try {
            $remote->directories('/');
        } catch (\Exception $e) {
            $this->error('Cannot access "'.$targetDisk.'" disk: '.$e->getMessage());

            return false;
        }

        $this->info('Both storage disks verified.');

        return true;
    }

    /**
     * Collects all files from the configured directories.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $local
     * @return array<int, string>
     */
    private function collectFiles($local): array
    {
        $allFiles = [];

        foreach (self::DIRECTORIES as $directory) {
            if (! $local->exists($directory)) {
                $this->warn("  Directory [{$directory}] not found on local disk, skipping.");

                continue;
            }

            $files = $local->allFiles($directory);
            $this->info("  [{$directory}] — ".count($files).' files found.');
            $allFiles = array_merge($allFiles, $files);
        }

        return $allFiles;
    }

    /**
     * Uploads files from local to remote disk.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $local
     * @param \Illuminate\Contracts\Filesystem\Filesystem $remote
     * @param array<int, string> $files
     * @return array{uploaded: int, skipped: int, failed: int, errors: array<int, string>}
     */
    private function uploadFiles($local, $remote, array $files, bool $overwrite): array
    {
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $results = ['uploaded' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];

        foreach ($files as $path) {
            try {
                if (! $overwrite && $remote->exists($path)) {
                    $results['skipped']++;
                    $bar->advance();

                    continue;
                }

                $stream = $local->readStream($path);

                if ($stream === null) {
                    $results['failed']++;
                    $results['errors'][] = "Failed to read: {$path}";
                    $bar->advance();

                    continue;
                }

                $remote->writeStream($path, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $results['uploaded']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "{$path}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Prints the upload summary.
     *
     * @param array{uploaded: int, skipped: int, failed: int, errors: array<int, string>} $results
     */
    private function printSummary(array $results): void
    {
        $this->info('=== File Migration Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Uploaded', $results['uploaded']],
                ['Skipped (already exists)', $results['skipped']],
                ['Failed', $results['failed']],
                ['Total', $results['uploaded'] + $results['skipped'] + $results['failed']],
            ]
        );

        if ($results['errors'] !== []) {
            $this->newLine();
            $this->error('Errors:');

            foreach ($results['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        if ($results['failed'] === 0) {
            $this->info('All files migrated successfully!');
        }
    }
}
