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
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;

final class UpdateUserAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user, private ?string $file = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('public');

        if ($this->user->avatar) {
            if ($disk->exists(str_replace('storage/', '', $this->user->avatar))) {
                $disk->delete(str_replace('storage/', '', $this->user->avatar));
            }
        }

        $file = $this->file !== null ? $this->file : (
            new Avatar($this->user->email, $this->user->github_username)
        )->url();

        $contents = (string) file_get_contents($file);

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$this->user->id).'.png';

        Storage::disk('public')->put($avatar, $contents, 'public');

        $this->resizer()->read($disk->path($avatar))
            ->resize(200, 200)
            ->save();

        $this->user->update([
            'avatar' => "storage/$avatar",
            'avatar_updated_at' => now(),
            'has_custom_avatar' => $this->file !== null,
        ]);
    }

    /**
     * Creates a new image resizer.
     */
    private function resizer(): ImageManager
    {
        return new ImageManager(
            new Drivers\Gd\Driver(),
        );
    }
}
