<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendEmailVerification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user->fresh();

        if ($user instanceof User && ! $user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmail);
        }
    }
}
