<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Avatar;
use App\Services\ImageOptimizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class UpdateUserAvatar implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user,
        private readonly ?string $file = null,
        private readonly ?string $service = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('public');

        if ($this->user->avatar && $disk->exists($this->user->avatar)) {
            $disk->delete($this->user->avatar);
        }

        $file = $this->file ?? (new Avatar($this->user))->url(
            $this->service ?? 'gravatar',
        );

        if ($file === asset('img/default-avatar.png')) {
            $this->user->update([
                'avatar' => null,
                'avatar_updated_at' => now(),
                'is_uploaded_avatar' => false,
            ]);

            return;
        }

        $contents = (string) file_get_contents($file);

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$this->user->id).'.png';

        Storage::disk('public')->put($avatar, $contents, 'public');

        $updated = $this->user->update([
            'avatar' => $avatar,
            'avatar_updated_at' => now(),
            'is_uploaded_avatar' => $this->file !== null,
        ]);

        if ($updated) {
            ImageOptimizer::optimize(
                path: $avatar,
                width: 300,
                height: 300,
                isThumbnail: true
            );
        }

        $this->ensureFileIsDeleted();
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->ensureFileIsDeleted();

        type($this->user->fresh())->as(User::class)->update([
            'avatar' => null,
            'avatar_updated_at' => null,
            'is_uploaded_avatar' => false,
        ]);
    }

    /**
     * Ensure the file is deleted.
     */
    private function ensureFileIsDeleted(): void
    {
        if (($this->file !== null) && File::exists($this->file)) {
            File::delete($this->file);
        }
    }
}
