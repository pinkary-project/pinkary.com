<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Avatar;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;
use Throwable;

#[DebounceFor(10, maxWait: 30)]
final class UpdateUserAvatar implements ShouldQueue
{
    use Queueable;

    /**
     * The avatar generation this job belongs to.
     */
    private ?CarbonInterface $generationAt;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user,
        private readonly ?string $file = null,
        private readonly ?string $service = null
    ) {
        $this->generationAt = $user->avatar_updated_at;
    }

    /**
     * Request a new avatar update, queuing the job.
     *
     * Bumping "avatar_updated_at" marks this request as the newest one,
     * superseding any pending job that was dispatched earlier.
     */
    public static function dispatchFor(User $user, ?string $file = null, ?string $service = null): void
    {
        self::markGeneration($user);

        self::dispatch($user, $file, $service);
    }

    /**
     * Request a new avatar update, running the job synchronously.
     */
    public static function dispatchForSync(User $user, ?string $file = null, ?string $service = null): void
    {
        self::markGeneration($user);

        self::dispatchSync($user, $file, $service);
    }

    /**
     * Scope the debounce to the user so that concurrent avatar updates of
     * different users never cancel each other.
     */
    public function debounceId(): string
    {
        return (string) $this->user->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user->fresh();

        if ($user === null || $this->isSuperseded($user)) {
            $this->ensureFileIsDeleted();

            return;
        }

        $disk = Storage::disk();

        if ($user->avatar && $disk->exists($user->avatar)) {
            $disk->delete($user->avatar);
        }

        $file = $this->file ?? new Avatar($user)->url(
            $this->service ?? ($user->github_username !== null ? 'github' : 'gravatar'),
        );

        if ($file === asset('img/default-avatar.png')) {
            $user->update([
                'avatar' => null,
                'avatar_updated_at' => now(),
                'is_uploaded_avatar' => false,
            ]);

            return;
        }

        if (str_contains($file, 'gravatar.com')) {
            $contents = Http::withoutVerifying()->get($file)->body();
        } else {
            $contents = (string) file_get_contents($file);
        }

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$user->id).'.png';

        $image = $this->resizer()->read($contents)
            ->coverDown(200, 200)->toPng()->toFilePointer();

        $disk->put($avatar, $image, ['visibility' => 'public']);

        $user->update([
            'avatar' => "$avatar",
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

        $user = $this->user->fresh();

        if ($user === null || $this->isSuperseded($user)) {
            return;
        }

        $user->update([
            'avatar' => null,
            'is_uploaded_avatar' => false,
        ]);
    }

    /**
     * Mark the given user as having requested a new avatar generation.
     */
    private static function markGeneration(User $user): void
    {
        $user->forceFill([
            'avatar_updated_at' => now(),
        ])->saveQuietly();
    }

    /**
     * Determine whether a newer avatar update was requested after this job.
     */
    private function isSuperseded(User $user): bool
    {
        $current = $user->avatar_updated_at;

        if ($current === null && ! $this->generationAt instanceof CarbonInterface) {
            return false;
        }

        if ($current === null || ! $this->generationAt instanceof CarbonInterface) {
            return true;
        }

        return $current->gt($this->generationAt);
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
            new Drivers\Imagick\Driver(),
            strip: true,
        );
    }
}
