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
    Storage::disk('public')->assertExists($user->avatar);
});

it('returns default avatar if not service or file passed', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user);

    $user = $user->fresh();

    expect($user->avatar)
        ->toBe(null)
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));
});

it('deletes the given avatar file', function () {
    Storage::fake('public');

    $contents = file_get_contents(public_path('img/default-avatar.png'));
    Storage::disk('public')->put('avatars/1.png', $contents, 'public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user, Storage::disk('public')->path('avatars/1.png'));

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk('public')->assertExists($user->avatar);

    Storage::disk('public')->assertMissing('avatars/1.png');
});

it('sets resets avatar state when job fails', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    expect(file_exists($file->getRealPath()))->toBeTrue();

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());
    (new UpdateUserAvatar($user))->failed(null);

    $user = $user->fresh();

    expect($user->avatar)->toBeNull();
    expect($file->getRealPath())->toBeFalse();
})->skipOnWindows(); // Skipped on Windows because of file permissions

it('accepts different services to download avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create(
        ['github_username' => 'CamKem']
    );

    UpdateUserAvatar::dispatchSync($user, service: 'github');

    $user->refresh();

    expect($user->avatar)
        ->toBeString()
        ->and(Storage::disk('public')
            ->exists($user->avatar)
        )
        ->toBeTrue();
});

it('defers to the default image if service avatar not found', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user, service: 'github');

    $user->refresh();

    expect($user->avatar)
        ->toBe(null)
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));

    $user->update(['avatar' => null]);

    UpdateUserAvatar::dispatchSync($user, service: 'gravatar');

    $user->refresh();

    expect($user->avatar)
        ->toBe(null)
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));
});
