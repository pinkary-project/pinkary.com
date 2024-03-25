<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\GitHub;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncVerifiedUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GitHub $github): void
    {
        $user = $this->user->fresh();
        assert($user instanceof User);

        $user->update([
            'is_verified' => $user->github_username && $github->isSponsoringUs($user->github_username),
        ]);
    }
}
