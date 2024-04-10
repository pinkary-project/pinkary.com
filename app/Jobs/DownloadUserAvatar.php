<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Avatar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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

        $this->deleteExistingAvatar();

        $this->user->update([
            'avatar' => 'storage/'.$avatar,
            'avatar_updated_at' => now(),
        ]);
    }

    /**
     * Fetch a new avatar for the user.
     */
    private function fetchNewAvatar(): string
    {
        /** @var array<int, string> $urls */
        $urls = $this->user->links->pluck('url')->values()->all();

        $avatar = new Avatar($this->user->email, $urls);
        $contents = app()->environment('testing') ? '...' : (string) file_get_contents($avatar->url());

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$this->user->id).'.png';

        Storage::disk('public')->put($avatar, $contents, 'public');

        return $avatar;
    }

    /**
     * Delete the current avatar.
     */
    private function deleteExistingAvatar(): void
    {
        $avatar = $this->user->avatar;

        if (! $avatar) {
            return;
        }

        if (! Storage::disk('public')->exists(str_replace('storage/', '', $avatar))) {
            return;
        }

        Storage::disk('public')->delete(str_replace('storage/', '', $avatar));
    }
}
