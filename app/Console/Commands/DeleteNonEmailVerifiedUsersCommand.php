<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

final class DeleteNonEmailVerifiedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:non-email-verified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete non-email verified users older than 24 hours.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::where('email_verified_at', null)
            ->where('updated_at', '<', now()->subDay())
            ->whereDoesntHave('links', function (Builder $query): void {
                $query->where('created_at', '<', now()->subDay());
            })
            ->whereDoesntHave('questionsSent', function (Builder $query): void {
                $query->where('created_at', '<', now()->subDay());
            })
            ->whereDoesntHave('questionsReceived', function (Builder $query): void {
                $query->where('created_at', '<', now()->subDay());
            })
            ->get()
            ->each
            ->purge();
    }
}
