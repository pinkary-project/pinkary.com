<?php

declare(strict_types=1);

use App\Actions\Profile\DeleteAvatar;

it('deletes the avatar', function () {
    $path = 'avatars/1.jpg';

    Storage::fake('public');

    Storage::disk('public')->put($path, '...');

    DeleteAvatar::execute($path);

    Storage::disk('public')->assertMissing($path);
});
