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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;
use Throwable;

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

        if ($this->user->avatar && $disk->exists(str_replace('storage/', '', (string) $this->user->avatar))) {
            $disk->delete(str_replace('storage/', '', (string) $this->user->avatar));
        }

        $file = $this->file ?? (new Avatar($this->user->email))->url();

        $contents = (string) file_get_contents($file);

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$this->user->id).'.png';

        Storage::disk('public')->put($avatar, $contents, 'public');

        $this->resizer()->read($disk->path($avatar))
            ->resize(200, 200)
            ->save();

        $this->user->update([
            'avatar' => "storage/$avatar",
            'avatar_updated_at' => now(),
            'is_uploaded_avatar' => $this->file !== null,
        ]);

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
        if ($this->file !== null) {
            File::delete($this->file);
        }
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
