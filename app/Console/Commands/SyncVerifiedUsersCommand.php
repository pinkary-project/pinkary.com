<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Console\Command;

final class SyncVerifiedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:verified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the "is_verified" column for all verified users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::where('is_verified', true)
            ->orWhere('is_company_verified', true)
            ->get()
            ->each(fn (User $user) => SyncVerifiedUser::dispatch($user));
    }
}
