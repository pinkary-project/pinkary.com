<?php

declare(strict_types=1);

use App\Jobs\DownloadUserAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('downloads user avatar', function () {
    Storage::disk('public')->assertDirectoryEmpty('avatars');

    $user = User::factory()->create();

    $job = new DownloadUserAvatar($user);

    $job->handle();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
});

test('ignores deleting avatar file if no longer exists', function () {
    $user = User::factory()->create([
        'avatar' => 'storage/avatars/default.png',
    ]);

    $job = new DownloadUserAvatar($user);

    $job->handle();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
    Storage::disk('public')->assertMissing('avatars/default.png');
});

test('deletes old avatar when downloading new one', function () {
    Storage::disk('public')->assertDirectoryEmpty('avatars');

    $user = User::factory()->create([
        'avatar' => 'storage/avatars/default.png',
    ]);

    Storage::disk('public')->put('avatars/default.png', '...');

    Storage::disk('public')->assertExists('avatars/default.png');

    $job = new DownloadUserAvatar($user);

    $job->handle();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
    Storage::disk('public')->assertMissing('avatars/default.png');
});
