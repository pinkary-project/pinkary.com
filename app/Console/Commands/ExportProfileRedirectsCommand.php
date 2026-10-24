<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ExportProfileRedirectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile-redirects:export {path? : The CSV file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export configured profile redirects for Cloudflare Bulk Redirects.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! str_starts_with(config()->string('app.url'), 'https://')) {
            $this->error('APP_URL must be an HTTPS URL before exporting profile redirects.');

            return self::FAILURE;
        }

        $path = $this->argument('path');

        if (! is_string($path) || $path === '') {
            $path = storage_path('app/profile-redirects.csv');
        }

        File::ensureDirectoryExists(dirname($path));

        if (is_dir($path)) {
            $this->error("Unable to open [{$path}] for writing.");

            return self::FAILURE;
        }

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            $this->error("Unable to open [{$path}] for writing.");

            return self::FAILURE;
        }

        $count = 0;

        try {
            User::query()
                ->whereNotNull('redirect_url')
                ->where('redirect_url', '!=', '')
                ->select(['username', 'redirect_url'])
                ->orderBy('id')
                ->cursor()
                ->each(function (User $user) use ($handle, &$count): void {
                    fputcsv($handle, [
                        route('profile.show', ['username' => $user->username]),
                        $user->redirect_url,
                        '301',
                    ], escape: '\\', eol: "\n");

                    $count++;
                });
        } finally {
            fclose($handle);
        }

        $this->info("Exported {$count} profile redirects to {$path}.");

        return self::SUCCESS;
    }
}
