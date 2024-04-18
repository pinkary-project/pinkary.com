<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class StoreAvatar
{
    /**
     * Store the avatar for the given user.
     */
    public static function execute(UploadedFile|string $file, int $userId): string
    {
        if ($file instanceof UploadedFile) {
            $contents = (string) file_get_contents($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
        } else {
            $contents = app()->environment('testing') ? '...' : (string) file_get_contents($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        $avatar = 'avatars/'.hash('sha256', random_int(0, PHP_INT_MAX).'@'.$userId).'.'.$extension;

        Storage::disk('public')->put($avatar, $contents, 'public');

        return $avatar;
    }
}
