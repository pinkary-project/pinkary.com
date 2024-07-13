<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guest', function () {
    $response = $this->get(route('profile.edit'));

    $response->assertRedirect('/login');
});

test('auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertSee([
        'Profile Information',
        "Update your account's profile information and email address.",
        'Update Password',
        'Ensure your account is using a long, random password to stay secure.',
        'Delete Account',
        'Once your account is deleted, all of its resources and data will be permanently deleted.',
    ]);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertSame('testuser', $user->username);
    $this->assertNull($user->email_verified_at);
    $this->assertFalse($user->prefers_anonymous_questions);
});

test('email provider must be authorized', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Tomás López',
            'username' => 'tomloprod',
            'email' => 'tomloprod@0-mail.com',
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => false,
        ]);

    $response
        ->assertStatus(302)
        ->assertSessionHasErrors([
            'email' => 'The email belongs to an unauthorized email provider.',
        ]);
});

test('username can be updated to uppercase', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => 'TESTUSER',
            'email' => $user->email,
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => false,
        ]);

    $response->assertSessionHasNoErrors();

    $this->get('/@testuser')->assertOk();
    $this->get('/@TESTUSER')->assertOk();
});

test('can not update to an existing username using uppercase', function () {
    User::factory()->create([
        'username' => 'testuser',
    ]);

    $user = User::factory()->create([
        'username' => 'testuser2',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => 'TESTUSER',
            'email' => $user->email,
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => true,
        ]);

    $response
        ->assertStatus(302)
        ->assertSessionHasErrors([
            'username' => 'The username has already been taken.',
        ]);

    $this->get('/@testuser')->assertOk();
    $this->get('/@TESTUSER')->assertOk();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => $user->email,
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('email verification job sent & status reset when the email address is changed', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => 'valid_username',
            'email' => 'new@email.address',
            'prefers_anonymous_questions' => false,
        ])
        ->assertSessionHasNoErrors();

    expect($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('only updates avatar if email changes & avatar not been uploaded', function () {
    Queue::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => 'valid_username',
            'email' => $user->email,
            'prefers_anonymous_questions' => false,
        ])
        ->assertSessionHasNoErrors();

    Queue::assertNotPushed(UpdateUserAvatar::class);
});

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('avatar is deleted when account is deleted', function () {
    $user = User::factory()->create([
        'avatar' => 'avatars/default.png',
    ]);

    Storage::disk('public')->put('avatars/default.png', '...');

    Storage::disk('public')->assertExists('avatars/default.png');

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    Storage::disk('public')->assertMissing('avatars/default.png');

    $response->assertSessionHasNoErrors();

    $this->assertNull($user->fresh());
    $this->assertFileDoesNotExist(storage_path('app/public/avatars/default.png'));
});

it('user can delete their account with followers', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    User::factory()->count(3)->create()->each(function ($follower) use ($user) {
        $follower->following()->attach($user);
    });
    expect($user->followers()->count())->toBe(3);

    $response = $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response->assertRedirect(url('/'));
    $this->assertNull($user->fresh());
});

it('user can delete their account with following', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    User::factory()->count(3)->create()->each(function ($following) use ($user) {
        $user->following()->attach($following);
    });
    expect($user->following()->count())->toBe(3);

    $response = $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response->assertRedirect(url('/'));
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test("can not update user's name with blank characters", function () {
    $user = User::factory()->state(['name' => 'Test User'])->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => "\u{200E}",
        ]);

    $response->assertSessionHasErrors(['name' => 'The name field is required.']);

    $this->assertSame('Test User', $user->name);
});

test('prefers_anonymous_questions can be updated', function () {
    $user = User::factory()->create([
        'prefers_anonymous_questions' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => $user->email,
            'mail_preference_time' => 'daily',
            'prefers_anonymous_questions' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($user->refresh()->prefers_anonymous_questions)->toBeFalse();
});

test('user can upload an avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->avatar)->toContain('avatars/')
        ->and($user->avatar)->toContain('.png')
        ->and($user->avatar_updated_at)->not()->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeTrue()
        ->and(session('flash-message'))->toBe('Avatar updated.');
});

test('user can delete custom avatar and update using Gravatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => 'avatars/avatar.jpg',
        'is_uploaded_avatar' => true,
        'email' => 'jitewaboh@lagify.com', // test Gravatar email
    ]);

    Storage::disk('public')->put('avatars/avatar.jpg', '...');

    $this->actingAs($user)
        ->delete('/profile/avatar')
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    Storage::disk('public')->assertMissing('avatars/avatar.jpg');

    $user->refresh();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar_updated_at)->not->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse()
        ->and(session('flash-message'))->toBe('Updating avatar using Gravatar.');
});

test('user can delete custom avatar and update using GitHub', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => 'avatars/avatar.jpg',
        'is_uploaded_avatar' => true,
        'github_username' => 'testuser',
    ]);

    Storage::disk('public')->put('avatars/avatar.jpg', '...');

    $this->actingAs($user)
        ->delete('/profile/avatar')
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    Storage::disk('public')->assertMissing('avatars/avatar.jpg');

    $user->refresh();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar_updated_at)->not->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse()
        ->and(session('flash-message'))->toBe('Updating avatar using GitHub.');
});

test('user can re-fetch avatar from Gravatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => 'avatars/avatar.jpg',
        'is_uploaded_avatar' => false,
        'email' => 'jitewaboh@lagify.com', // test Gravatar email
    ]);

    $this->actingAs($user)
        ->delete('/profile/avatar')
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar_updated_at)->not->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse()
        ->and(session('flash-message'))->toBe('Updating avatar using Gravatar.');
});

test('user can re-fetch avatar from GitHub', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => 'avatars/avatar.jpg',
        'is_uploaded_avatar' => false,
        'github_username' => 'testuser',
    ]);

    $this->actingAs($user)
        ->delete('/profile/avatar')
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar_updated_at)->not->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse()
        ->and(session('flash-message'))->toBe('Updating avatar using GitHub.');
});
