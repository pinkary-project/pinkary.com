<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class DeleteNonVerifiedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:non-verified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the non-verified users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::where('email_verified_at', null)
            ->where('updated_at', '<', now()->subDay())
            ->get()
            ->each
            ->delete();
    }
}
