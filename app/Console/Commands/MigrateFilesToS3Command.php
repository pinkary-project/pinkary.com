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
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDisk = (string) $this->option('disk');
        $sourceDisk = (string) $this->option('source');
        $overwrite = (bool) $this->option('overwrite');

        if ($sourceDisk === $targetDisk) {
            $this->error('The source and target disks must be different.');

            return self::FAILURE;
        }

        $local = Storage::disk($sourceDisk);
        $remote = Storage::disk($targetDisk);

        if (! $this->validateDisks($local, $remote, $sourceDisk, $targetDisk)) {
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
     */
    private function validateDisks(
        Filesystem $local,
        Filesystem $remote,
        string $sourceDisk,
        string $targetDisk,
    ): bool {
        try {
            $local->directories('/');
        } catch (Exception $e) {
            $this->error('Cannot access "'.$sourceDisk.'" disk: '.$e->getMessage());

            return false;
        }

        try {
            $remote->directories('/');
        } catch (Exception $e) {
            $this->error('Cannot access "'.$targetDisk.'" disk: '.$e->getMessage());

            return false;
        }

        $this->info('Both storage disks verified.');

        return true;
    }

    /**
     * Collects all files from the configured directories.
     *
     * @return array<array-key, string>
     */
    private function collectFiles(Filesystem $local): array
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
     * @param  array<array-key, string>  $files
     * @return array{uploaded: int, skipped: int, failed: int, errors: array<int, string>}
     */
    private function uploadFiles(Filesystem $local, Filesystem $remote, array $files, bool $overwrite): array
    {
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $results = ['uploaded' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];

        foreach ($files as $path) {
            $stream = null;

            try {
                if (! $overwrite && $remote->exists($path)) {
                    if ($local->size($path) !== $remote->size($path)) {
                        throw new Exception('Remote file exists with a different size; rerun with --overwrite.');
                    }

                    $results['skipped']++;

                    continue;
                }

                $stream = $local->readStream($path);

                if ($stream === null) {
                    $results['failed']++;
                    $results['errors'][] = "Failed to read: {$path}";

                    continue;
                }

                if (! $remote->writeStream($path, $stream, ['visibility' => 'public'])) {
                    throw new Exception('The target disk rejected the upload.');
                }

                if ($local->size($path) !== $remote->size($path)) {
                    throw new Exception('Uploaded file size does not match the source.');
                }

                $results['uploaded']++;
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "{$path}: {$e->getMessage()}";
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Prints the upload summary.
     *
     * @param  array{uploaded: int, skipped: int, failed: int, errors: array<int, string>}  $results
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
