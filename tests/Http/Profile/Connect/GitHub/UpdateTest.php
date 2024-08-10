<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

test('guest', function () {
    $response = $this->get(route('profile.connect.github.update'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('connect github', function () {
    Http::fake();

    $user = User::factory()->create();
    expect($user->github_username)->toBeNull();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');

    $response = $this->actingAs($user)->get(route('profile.connect.github.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your GitHub account has been connected.');

    $user->refresh();

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeFalse();

});

test('connecting to same github username', function () {
    $user = User::factory()->create([
        'github_username' => 'test',
    ]);
    expect($user->github_username)->toBe('test');

    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');

    $this->withoutExceptionHandling();

    $response = $this->actingAs($user)->get(route('profile.connect.github.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('The same GitHub account has been connected.');

    $user->refresh();

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeFalse();
});

test('connecting to existing github username', function () {
    User::factory()->create([
        'github_username' => 'test',
    ]);

    $user = User::factory()->create();

    expect($user->github_username)->toBeNull();

    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');

    $response = $this->actingAs($user)->get(route('profile.connect.github.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    $response->assertSessionHasErrors('github_username', 'This GitHub username is already connected to another account.', 'verified');

    $user->refresh();

    expect($user->github_username)->toBeNull();
    expect($user->is_verified)->toBeFalse();
});

test('connect to github may get you verified if you are sponsoring us', function () {
    $user = User::factory()->create();
    expect($user->is_verified)->toBeFalse();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');

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

    $response = $this->actingAs($user)->get(route('profile.connect.github.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your GitHub account has been connected and you are now verified.');

    $user->refresh();

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeTrue();
});

test('fetches github avatar if no custom avatar uploaded', function () {
    Http::fake();
    Queue::fake();

    $user = User::factory()->create();
    expect($user->github_username)->toBeNull();
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('test');

    $this->actingAs($user)
        ->get(route('profile.connect.github.update'))
        ->assertStatus(302)
        ->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))
        ->toBe('Your GitHub account has been connected.');

    Queue::assertPushed(UpdateUserAvatar::class);

    $job = new UpdateUserAvatar(
        user: $user,
        service: 'github',
    );

    $job->handle();

    expect($job)->toBeInstanceOf(UpdateUserAvatar::class);

    $user->refresh();

    expect($user->github_username)->toBe('test')
        ->and($user->is_verified)->toBeFalse()
        ->and($user->avatar)->toContain('avatars/')
        ->and($user->avatar)->toContain('.png')
        ->and($user->avatar_updated_at)->not()->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse();

});
