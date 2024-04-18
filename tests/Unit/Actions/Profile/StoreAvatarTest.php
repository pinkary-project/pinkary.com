<?php

declare(strict_types=1);

use App\Actions\Profile\StoreAvatar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a file base avatar', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('avatar.jpg');
    $userId = 1;

    $location = StoreAvatar::execute($file, $userId);

    Storage::disk('public')->assertExists($location);

    expect($location)->toContain('avatars/');
});

it('stores a string based avatar', function () {
    Storage::fake('public');

    $file = 'https://i.pravatar.cc/300';
    $userId = 1;

    $location = StoreAvatar::execute($file, $userId);

    Storage::disk('public')->assertExists($location);

    expect($location)->toContain('avatars/');
});
