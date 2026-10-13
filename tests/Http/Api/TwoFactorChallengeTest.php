<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

function twoFactorUser(): User
{
    return User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => encrypt('ABCDEFGHIJKLMNOP'),
        'two_factor_confirmed_at' => now(),
    ]);
}

function currentOtp(User $user): string
{
    return app(Google2FA::class)->getCurrentOtp(
        Laravel\Fortify\Fortify::currentEncrypter()->decrypt($user->fresh()->two_factor_secret),
    );
}

test('a user with two factor enabled is challenged instead of given a token', function (): void {
    $user = twoFactorUser();

    $response = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertStatus(422)
        ->assertJsonPath('code', 'two_factor_required')
        ->assertJsonStructure(['challenge', 'expires_in']);

    expect($response->json('challenge'))->toBeString()
        ->and($response->json('errors'))->toBeNull();
});

test('the challenge does not leak the account before the password is checked', function (): void {
    $user = twoFactorUser();

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(422)
        ->assertJsonMissingPath('challenge')
        ->assertJsonValidationErrors('email');
});

test('a correct one time password mints the token', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $response = $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => currentOtp($user),
    ])->assertOk();

    expect($response->json('token'))->toBeString();

    $this->withToken($response->json('token'))
        ->getJson(route('api.v1.profile.show'))
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

test('a wrong one time password is rejected', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => '000000',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

test('a recovery code is accepted once and then spent', function (): void {
    $user = twoFactorUser();
    $user->forceFill(['two_factor_recovery_codes' => encrypt(json_encode(['a-recovery-code']))])->save();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'recovery_code' => 'a-recovery-code',
    ])->assertOk()
        ->assertJsonStructure(['token']);

    $second = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $second,
        'recovery_code' => 'a-recovery-code',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

test('a spent challenge cannot be answered again with the same code', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $code = currentOtp($user);

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => $code,
    ])->assertOk();

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => $code,
    ])->assertStatus(422)
        ->assertJsonValidationErrors('challenge');
});

test('a forged or tampered challenge is rejected', function (): void {
    $user = twoFactorUser();

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => 'not-a-real-challenge',
        'code' => '123456',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('challenge');

    $challenge = app(App\Services\TwoFactorChallenge::class)->issue($user);

    $this->travel(6)->minutes();

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => currentOtp($user),
    ])->assertStatus(422)
        ->assertJsonValidationErrors('challenge');
});

test('a challenge stops working once two factor is turned off', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $user->forceFill(['two_factor_confirmed_at' => null])->save();

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => currentOtp($user),
    ])->assertStatus(422)
        ->assertJsonValidationErrors('challenge');
});

test('a challenge answered without any code is rejected', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
    ])->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

test('a user who has not confirmed two factor still logs in normally', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => encrypt('a-secret'),
        'two_factor_confirmed_at' => null,
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['token', 'data']);
});

test('a corrupt stored secret fails validation instead of erroring', function (): void {
    $user = twoFactorUser();
    $user->forceFill(['two_factor_secret' => encrypt('not base32 at all!')])->save();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => '123456',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

test('two factor answers are throttled', function (): void {
    $user = twoFactorUser();

    $challenge = $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('challenge');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
            'challenge' => $challenge,
            'code' => '000000',
        ])->assertStatus(422)->assertJsonValidationErrors('code');
    }

    $this->postJson(route('api.v1.auth.login.two-factor-challenge'), [
        'challenge' => $challenge,
        'code' => currentOtp($user),
    ])->assertStatus(429);
});
