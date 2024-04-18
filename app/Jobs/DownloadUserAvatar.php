<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Profile\DeleteAvatar;
use App\Actions\Profile\StoreAvatar;
use App\Models\User;
use App\Services\Avatar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class DownloadUserAvatar implements ShouldQueue
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
    public function handle(): void
    {
        $avatar = $this->fetchNewAvatar();

        if ($this->user->avatar) {
            DeleteAvatar::execute($this->user->avatar);
        }

        $location = StoreAvatar::execute($avatar->url(), $this->user->id);

        $this->user->update([
            'avatar' => 'storage/'.$location,
            'avatar_updated_at' => now(),
            'has_custom_avatar' => false,
        ]);
    }

    /**
     * Fetch a new avatar for the user.
     */
    private function fetchNewAvatar(): Avatar
    {
        /** @var array<int, string> $urls */
        $urls = $this->user->links->pluck('url')->values()->all();

        return new Avatar($this->user->email, $urls);
    }
}
