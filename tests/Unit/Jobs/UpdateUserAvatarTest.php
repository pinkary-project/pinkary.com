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

it('deletes the given avatar file', function () {
    Storage::fake('public');

    $contents = file_get_contents(public_path('img/default-avatar.png'));
    Storage::disk('public')->put('avatars/1.png', $contents, 'public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user, Storage::disk('public')->path('avatars/1.png'));

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));

    Storage::disk('public')->assertMissing('avatars/1.png');
});
