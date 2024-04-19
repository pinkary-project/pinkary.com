<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a file base avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
});

it('stores a url base avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user);

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
});

it('deletes the previous avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());
    $previousAvatar = $user->fresh()->avatar;

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());
    $currentAvatar = $user->fresh()->avatar;

    expect($currentAvatar)->not->toBe($previousAvatar);

    Storage::disk('public')->assertMissing(str_replace('storage/', '', $previousAvatar));
    Storage::disk('public')->assertExists(str_replace('storage/', '', $currentAvatar));
});
