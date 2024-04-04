<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\freezeTime;
use function Pest\Laravel\get;

it('redirects to github', function (): void {
    $response = get(route('auth.github.redirect'));

    $response->assertStatus(302);
    $response->assertRedirectContains('https://github.com/login/oauth/authorize');
})->only();

it('can authenticate with GitHub', function (): void {
    freezeTime();

    Socialite::shouldReceive('driver->user')->andReturn((new SocialiteUser())->map([
        'email' => 'test@example.com',
        'name' => 'Test User',
        'nickname' => 'test',
    ]));

    $response = get(route('auth.github.callback'));

    $response
        ->assertStatus(302)
        ->assertRedirectToRoute('profile.edit');

    expect(session('flash-message'))->toBe('Your GitHub account has been connected.');

    assertAuthenticated();

    assertDatabaseHas(User::class, [
        'email' => 'test@example.com',
        'email_verified_at' => now(),
        'github_username' => 'test',
        'name' => 'Test User',
        'username' => 'test',
    ]);
})->only();
