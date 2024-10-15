<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Facades\Socialite;

test('log in using GitHub', function () {
    Http::fake();

    $user = User::factory()->create();
    expect($user->github_username)->toBeNull();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');
    Socialite::shouldReceive('driver->user->getEmail')->andReturn($user->email);

    $response = $this->get(route('profile.connect.github.login.callback'));

    $response->assertStatus(302);
    $response->assertRedirect(route('home.feed'));

    $this->assertAuthenticated();

    expect(session('flash-message'))->toBe('Your GitHub account has been connected.');

    $user->refresh();

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeFalse();
});

test('log in using GitHub when existing GitHub username', function () {
    User::factory()->create([
        'github_username' => 'test',
    ]);

    $user = User::factory()->create();

    expect($user->github_username)->toBeNull();

    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');
    Socialite::shouldReceive('driver->user->getEmail')->andReturn($user->email);

    $response = $this->get(route('profile.connect.github.login.callback'));

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));

    $this->assertGuest();

    $response->assertSessionHasErrors('github_username', 'This GitHub username is already connected to another account.', 'github');

    $user->refresh();

    expect($user->github_username)->toBeNull();
    expect($user->is_verified)->toBeFalse();
});

test('log in uisng GitHub may get you verified if you are sponsoring us', function () {
    $user = User::factory()->create();
    expect($user->is_verified)->toBeFalse();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');
    Socialite::shouldReceive('driver->user->getEmail')->andReturn($user->email);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [
                        [
                            'monthlyPriceInDollars' => 9,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->get(route('profile.connect.github.login.callback'));

    $response->assertStatus(302);
    $response->assertRedirect(route('home.feed'));

    expect(session('flash-message'))->toBe('Your GitHub account has been connected and you are now verified.');

    $user->refresh();

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeTrue();
});

test('log in uisng GitHub fetches github avatar if no custom avatar uploaded', function () {
    Http::fake();
    Queue::fake();

    $user = User::factory()->create();
    expect($user->github_username)->toBeNull();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');
    Socialite::shouldReceive('driver->user->getEmail')->andReturn($user->email);

    $this->get(route('profile.connect.github.login.callback'))
        ->assertStatus(302)
        ->assertRedirect(route('home.feed'));

    expect(session('flash-message'))
        ->toBe('Your GitHub account has been connected.');

    Queue::assertPushed(UpdateUserAvatar::class);

    $user->refresh();

    $job = new UpdateUserAvatar(
        user: $user,
        service: 'github',
    );

    $job->handle();

    expect($job)->toBeInstanceOf(UpdateUserAvatar::class);

    expect($user->github_username)->toBe('test')
        ->and($user->is_verified)->toBeFalse()
        ->and($user->avatar)->toContain('avatars/')
        ->and($user->avatar)->toContain('.png')
        ->and($user->avatar_updated_at)->not()->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse();
});

test('log in using GitHub send to register if user not found', function () {
    Http::fake();

    $user = User::factory()->create();
    expect($user->github_username)->toBeNull();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');
    Socialite::shouldReceive('driver->user->getEmail')->andReturn('other@example.com');

    $response = $this->get(route('profile.connect.github.login.callback'));

    $response->assertStatus(302);
    $response->assertRedirect(route('register'));

    expect(session('github_email'))->toBe('other@example.com');
    expect(session('github_username'))->toBe('test');

    $this->assertGuest();

    $user->refresh();

    expect($user->github_username)->toBeNull();
    expect($user->is_verified)->toBeFalse();
});
