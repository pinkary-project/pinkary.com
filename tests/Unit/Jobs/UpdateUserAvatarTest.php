<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a file base avatar', function (): void {
    Storage::fake();

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk()->assertExists($user->avatar);
});

it('returns default avatar if not service or file passed', function (): void {
    Storage::fake();

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user);

    $user = $user->fresh();

    expect($user->avatar)->toBeNull()
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));
});

it('deletes the given avatar file', function (): void {
    Storage::fake();

    $contents = file_get_contents(public_path('img/default-avatar.png'));
    Storage::disk()->put('avatars/1.png', $contents, 'public');

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user, Storage::disk()->path('avatars/1.png'));

    $user = $user->fresh();

    expect($user->avatar)->toBeString();
    Storage::disk()->assertExists($user->avatar);

    Storage::disk()->assertMissing('avatars/1.png');
});

it('sets resets avatar state when job fails', function (): void {
    Storage::fake();

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    expect(file_exists($file->getRealPath()))->toBeTrue();

    UpdateUserAvatar::dispatchSync($user, $file->getRealPath());
    new UpdateUserAvatar($user->fresh())->failed(null);

    $user = $user->fresh();

    expect($user->avatar)->toBeNull()
        ->and($file->getRealPath())->toBeFalse();
})->skipOnWindows(); // Skipped on Windows because of file permissions

it('skips a superseded job so the newest avatar request wins', function (): void {
    Storage::fake();

    $user = User::factory()->create(['github_username' => 'CamKem']);

    $staleJob = new UpdateUserAvatar($user);

    $this->travel(1)->seconds();

    UpdateUserAvatar::dispatchForSync($user, service: 'github');

    $avatar = $user->refresh()->avatar;
    expect($avatar)->toBeString();

    $staleJob->handle();

    expect($user->refresh()->avatar)->toBe($avatar)
        ->and(Storage::disk()->exists((string) $avatar))->toBeTrue();
});

it('does not reset the avatar when the failing job is superseded', function (): void {
    Storage::fake();

    $user = User::factory()->create(['github_username' => 'CamKem']);

    $staleJob = new UpdateUserAvatar($user);

    $this->travel(1)->seconds();

    UpdateUserAvatar::dispatchForSync($user, service: 'github');

    $avatar = $user->refresh()->avatar;
    expect($avatar)->toBeString();

    $staleJob->failed(null);

    expect($user->refresh()->avatar)->toBe($avatar);
});

it('preserves the superseding token when the job fails', function (): void {
    Storage::fake();

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user);

    $token = $user->refresh()->avatar_updated_at;

    expect($token)->not()->toBeNull();

    new UpdateUserAvatar($user->fresh())->failed(null);

    $user->refresh();

    expect($user->avatar_updated_at)->toEqual($token)
        ->and($user->avatar)->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse();
});

it('accepts different services to download avatar', function (): void {
    Storage::fake();

    $user = User::factory()->create(
        ['github_username' => 'CamKem']
    );

    UpdateUserAvatar::dispatchSync($user, service: 'github');

    $user->refresh();

    expect($user->avatar)
        ->toBeString()
        ->and(Storage::disk()
            ->exists($user->avatar)
        )
        ->toBeTrue();
});

it('defers to the default image if service avatar not found', function (): void {
    Storage::fake();

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchSync($user, service: 'github');

    $user->refresh();

    expect($user->avatar)->toBeNull()
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));

    $user->update(['avatar' => null]);

    UpdateUserAvatar::dispatchSync($user, service: 'gravatar');

    $user->refresh();

    expect($user->avatar)->toBeNull()
        ->and($user->avatar_url)
        ->toBe(asset('img/default-avatar.png'));
});

it('is superseded when a newer generation is requested after dispatch', function (): void {
    Storage::fake();

    $user = User::factory()->create();

    UpdateUserAvatar::dispatchFor($user);

    $staleJob = new UpdateUserAvatar($user->fresh(), UploadedFile::fake()->image('avatar.jpg')->getRealPath());

    UpdateUserAvatar::dispatchFor($user);

    $avatarAfterNewest = $user->fresh()->avatar;

    $staleJob->handle();

    expect($user->fresh()->avatar)->toBe($avatarAfterNewest)
        ->and($user->fresh()->avatar_generation)->toBe(2);
});
