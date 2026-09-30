<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a user can register through the API', function (): void {
    $response = $this->postJson(route('api.v1.auth.register'), [
        'name' => 'Pinkary User',
        'username' => 'pinkaryuser',
        'email' => 'pinkary@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data', 'token'])
        ->assertJsonPath('data.username', 'pinkaryuser');

    expect(User::query()->where('email', 'pinkary@example.com')->exists())->toBeTrue();
});

test('a user can log in through the API', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['data', 'token'])
        ->assertJsonPath('data.id', $user->id);
});

test('invalid credentials are rejected', function (): void {
    $this->postJson(route('api.v1.auth.login'), [
        'email' => 'pinkary@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('a user can revoke the current API token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $headers = ['Authorization' => 'Bearer '.$token];

    $this->postJson(route('api.v1.auth.logout'), [], $headers)->assertNoContent();

    auth()->forgetGuards();

    $this->getJson(route('api.v1.profile.show'), $headers)->assertUnauthorized();
});

test('registration validates input and rejects duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson(route('api.v1.auth.register'), [
        'name' => 'Pinkary User',
        'username' => 'pinkaryuser',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('api routes render json exceptions even without accept header', function (): void {
    $this->get('/api/v1/questions/non-existent-uuid')
        ->assertNotFound()
        ->assertHeader('content-type', 'application/json');
});

test('a user with two factor authentication enabled cannot log in', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => 'a-secret',
        'two_factor_confirmed_at' => now(),
    ]);

    // The web challenges a 2FA account (routes/auth.php:38-42). The API has
    // no 2FA screen to challenge in, so it must refuse rather than mint a
    // bearer token that silently bypasses the setting.
    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    expect($user->tokens()->count())->toBe(0);
});

test('a user who has not confirmed two factor can still log in', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => 'a-secret',
        'two_factor_confirmed_at' => null,
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['token']);
});

test('a minted token carries an expiry', function (): void {
    expect(config('sanctum.expiration'))->toBe(90 * 24 * 60);

    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    expect($user->tokens()->first()->expires_at)->not->toBeNull();
});

test('an expired token is rejected', function (): void {
    $user = User::factory()->create();

    // A token that was minted 91 days ago. Sanctum checks expires_at, so
    // this is refused even though it is still a structurally valid token.
    $expired = $user->tokens()->create([
        'name' => 'old-device',
        'token' => hash('sha256', 'plain-text-secret'),
        'abilities' => ['*'],
        'expires_at' => now()->subDay(),
    ]);

    $headers = ['Authorization' => 'Bearer '.$expired->id.'|plain-text-secret'];

    $this->getJson(route('api.v1.profile.show'), $headers)->assertUnauthorized();
});

test('a token inside its lifetime still works', function (): void {
    $user = User::factory()->create();

    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.profile.show'), $headers)->assertOk();
});

test('logging in returns the signed-in user own email and verification state', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
    ]);

    // No token exists yet at this point in the request, so the resource
    // cannot read the viewer off auth(). Before this was fixed the client
    // received its own email and verification.email as null.
    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('data.email', 'pinkary@example.com')
        ->assertJsonPath('data.verification.email', true)
        ->assertJsonPath('data.is_me', true);
});

test('registering returns the new user own email and unverified state', function (): void {
    // A brand new account is unverified by definition, and that is the one
    // thing it most needs to be told.
    $this->postJson(route('api.v1.auth.register'), [
        'name' => 'Pinkary User',
        'username' => 'pinkaryuser',
        'email' => 'pinkary@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ])->assertCreated()
        ->assertJsonPath('data.email', 'pinkary@example.com')
        ->assertJsonPath('data.verification.email', false)
        ->assertJsonPath('data.is_me', true);
});

test('the login response carries the profile counts and links, not empty ones', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('data.stats.followers', 0)
        ->assertJsonPath('data.stats.following', 0)
        ->assertJsonPath('data.stats.posts', 0)
        ->assertJsonPath('data.followed_by_me', false)
        ->assertJsonPath('data.follows_me', false)
        ->assertJsonStructure(['data' => ['links']]);
});
