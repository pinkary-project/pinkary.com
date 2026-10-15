<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Account\GitHubController;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpKernel\Exception\HttpException;

function mobileAccountApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('account endpoints require an API token', function (): void {
    $this->getJson(route('api.v1.account.email-verification.show'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.email-verification.notification'))->assertUnauthorized();
    $this->putJson(route('api.v1.account.password.update'))->assertUnauthorized();
    $this->getJson(route('api.v1.account.two-factor.show'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.two-factor.store'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.two-factor.confirm'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.account.two-factor.destroy'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.two-factor.recovery-codes.show'))->assertUnauthorized();
    $this->putJson(route('api.v1.account.two-factor.recovery-codes.update'))->assertUnauthorized();
    $this->getJson(route('api.v1.account.github.show'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.github.handoff.store'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.account.github.destroy'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.account.tokens.destroy'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.account.destroy'))->assertUnauthorized();
    $this->postJson(route('api.v1.account.avatar.refresh'))->assertUnauthorized();
    $this->deleteJson(route('api.v1.account.avatar.destroy'))->assertUnauthorized();
});

test('an API user can read their email verification status', function (): void {
    $user = User::factory()->unverified()->create();

    $this->withToken(mobileAccountApiToken($user))
        ->getJson(route('api.v1.account.email-verification.show'))
        ->assertOk()
        ->assertJsonPath('email', $user->email)
        ->assertJsonPath('verified', false)
        ->assertJsonPath('verified_at', null);

    $user->forceFill(['email_verified_at' => now()])->save();
    auth()->forgetGuards();

    $this->withToken(mobileAccountApiToken($user))
        ->getJson(route('api.v1.account.email-verification.show'))
        ->assertOk()
        ->assertJsonPath('verified', true)
        ->assertJsonPath('verified_at', $user->fresh()->email_verified_at->toIso8601String());
});

test('an API user can request email verification without resending after verification', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->postJson(route('api.v1.account.email-verification.notification'))
        ->assertAccepted()
        ->assertJsonPath('verified', false);

    Notification::assertSentTo($user, VerifyEmail::class);

    Notification::fake();
    $user->forceFill(['email_verified_at' => now()])->save();
    auth()->forgetGuards();

    $this->withToken($token)
        ->postJson(route('api.v1.account.email-verification.notification'))
        ->assertAccepted()
        ->assertJsonPath('verified', true);

    Notification::assertNothingSent();
});

test('an API user can revoke all tokens after confirming their password', function (): void {
    $user = User::factory()->create();
    $user->createToken('another device');
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->deleteJson(route('api.v1.account.tokens.destroy'), ['current_password' => 'password'])
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);

    $this->withToken(mobileAccountApiToken($user))
        ->deleteJson(route('api.v1.account.tokens.destroy'), ['current_password' => 'wrong-password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

test('an API user can delete their account after confirming their password', function (): void {
    $user = User::factory()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->deleteJson(route('api.v1.account.destroy'), ['password' => 'password'])
        ->assertNoContent();

    expect(User::query()->find($user->id))->toBeNull();
});

test('an API user can refresh or remove their avatar', function (): void {
    Bus::fake([UpdateUserAvatar::class]);

    $user = User::factory()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->postJson(route('api.v1.account.avatar.refresh'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    $this->withToken($token)
        ->deleteJson(route('api.v1.account.avatar.destroy'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    Bus::assertDispatchedSyncTimes(UpdateUserAvatar::class, 2);
});

test('an API user can request a password reset without revealing whether the account exists', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->postJson(route('api.v1.account.password.recovery'), ['email' => $user->email])
        ->assertAccepted()
        ->assertJsonPath('message', 'If an account exists for that email, a password reset link has been sent.');

    Notification::assertSentTo($user, ResetPassword::class);

    $this->postJson(route('api.v1.account.password.recovery'), ['email' => 'missing@example.com'])
        ->assertAccepted()
        ->assertJsonPath('message', 'If an account exists for that email, a password reset link has been sent.');
});

test('an API user can reset their password and existing tokens are revoked', function (): void {
    Notification::fake();
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create();
    $user->createToken('old device');
    $token = Password::createToken($user);

    $this->postJson(route('api.v1.account.password.reset'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(0)
        ->and(Hash::check('new-password', $user->fresh()->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);

    $this->postJson(route('api.v1.account.password.reset'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'another-password',
        'password_confirmation' => 'another-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('password reset rejects an invalid broker response type', function (): void {
    Password::shouldReceive('reset')->once()->andReturn(null);
    $this->withoutExceptionHandling();

    expect(fn () => $this->postJson(route('api.v1.account.password.reset'), [
        'token' => 'valid-token',
        'email' => 'user@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]))->toThrow(UnexpectedValueException::class);
});

test('an API user can change their password after confirming the current password', function (): void {
    $user = User::factory()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->putJson(route('api.v1.account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertNoContent();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();

    $this->withToken($token)
        ->putJson(route('api.v1.account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

test('an API user can configure and disable two-factor authentication', function (): void {
    $user = User::factory()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->getJson(route('api.v1.account.two-factor.show'))
        ->assertOk()
        ->assertJsonPath('enabled', false)
        ->assertJsonPath('pending_confirmation', false);

    $setup = $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.store'), ['current_password' => 'password'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('pending_confirmation', true)
        ->assertJsonStructure(['secret', 'qr_code_url', 'qr_code_svg'])
        ->json();

    $this->withToken($token)
        ->getJson(route('api.v1.account.two-factor.show'))
        ->assertOk()
        ->assertJsonPath('enabled', false)
        ->assertJsonPath('pending_confirmation', true);

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.confirm'), [
            'current_password' => 'password',
            'code' => app(Google2FA::class)->getCurrentOtp($setup['secret']),
        ])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('enabled', true)
        ->assertJsonStructure(['recovery_codes']);

    $this->withToken($token)
        ->getJson(route('api.v1.account.two-factor.show'))
        ->assertOk()
        ->assertJsonPath('enabled', true)
        ->assertJsonPath('pending_confirmation', false);

    $codes = $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.recovery-codes.show'), ['current_password' => 'password'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->json('recovery_codes');

    $newCodes = $this->withToken($token)
        ->putJson(route('api.v1.account.two-factor.recovery-codes.update'), ['current_password' => 'password'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->json('recovery_codes');

    expect($newCodes)->not->toBe($codes);

    $this->withToken($token)
        ->deleteJson(route('api.v1.account.two-factor.destroy'), ['current_password' => 'password'])
        ->assertNoContent();

    $this->withToken($token)
        ->getJson(route('api.v1.account.two-factor.show'))
        ->assertOk()
        ->assertJsonPath('enabled', false)
        ->assertJsonPath('pending_confirmation', false);
});

test('two-factor endpoints reject missing setup, invalid codes, and disabled recovery requests', function (): void {
    $user = User::factory()->create();
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.confirm'), [
            'current_password' => 'password',
            'code' => '123456',
        ])
        ->assertConflict();

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.recovery-codes.show'), ['current_password' => 'password'])
        ->assertConflict();

    $this->withToken($token)
        ->putJson(route('api.v1.account.two-factor.recovery-codes.update'), ['current_password' => 'password'])
        ->assertConflict();

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.store'), ['current_password' => 'password'])
        ->assertOk();

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.confirm'), [
            'current_password' => 'password',
            'code' => '000000',
        ])
        ->assertUnprocessable();

    $user->forceFill([
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
    ])->save();
    auth()->forgetGuards();

    $this->withToken($token)
        ->postJson(route('api.v1.account.two-factor.store'), ['current_password' => 'password'])
        ->assertConflict();

});

test('an API user cannot confirm two-factor authentication a second time', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('ABCDEFGHIJKLMNOP'),
        'two_factor_confirmed_at' => now(),
    ]);
    $secret = Laravel\Fortify\Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

    $this->withToken(mobileAccountApiToken($user))
        ->postJson(route('api.v1.account.two-factor.confirm'), [
            'current_password' => 'password',
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
        ])
        ->assertConflict();
});

test('two-factor disable is safe when setup has not been started', function (): void {
    $user = User::factory()->create();

    $this->withToken(mobileAccountApiToken($user))
        ->deleteJson(route('api.v1.account.two-factor.destroy'), ['current_password' => 'password'])
        ->assertNoContent();
});

test('two-factor setup reports when Fortify does not create a secret', function (): void {
    $enable = Mockery::mock(EnableTwoFactorAuthentication::class);
    $enable->shouldReceive('__invoke')->once();
    $this->app->instance(EnableTwoFactorAuthentication::class, $enable);

    $user = User::factory()->create();

    $this->withToken(mobileAccountApiToken($user))
        ->postJson(route('api.v1.account.two-factor.store'), ['current_password' => 'password'])
        ->assertConflict();
});

test('an API user can inspect their GitHub connection and start a private handoff', function (): void {
    config([
        'services.github.client_id' => 'client-id',
        'services.github.client_secret' => 'client-secret',
        'services.github.redirect' => 'https://pinkary.test/profile/connect/github/update',
    ]);

    $user = User::factory()->create(['github_username' => null]);
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->getJson(route('api.v1.account.github.show'))
        ->assertOk()
        ->assertJsonPath('connected', false)
        ->assertJsonPath('eligible', true)
        ->assertJsonPath('eligibility_reason', null);

    $handoff = $this->withToken($token)
        ->postJson(route('api.v1.account.github.handoff.store'))
        ->assertCreated()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('expires_in', 600)
        ->json();

    expect($handoff['browser_url'])->toContain('/mobile/github/handoff/'.$handoff['id'].'/');
    $this->assertDatabaseHas('mobile_github_handoffs', [
        'id' => $handoff['id'],
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $this->withToken($token)
        ->getJson(route('api.v1.account.github.handoff.show', $handoff['id']))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('id', $handoff['id'])
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('github_username', null);

    $otherUser = User::factory()->create();
    auth()->forgetGuards();

    $this->withToken(mobileAccountApiToken($otherUser))
        ->getJson(route('api.v1.account.github.handoff.show', $handoff['id']))
        ->assertNotFound();
});

test('GitHub connection reports unavailable and already-connected states', function (): void {
    config([
        'services.github.client_id' => null,
        'services.github.client_secret' => null,
        'services.github.redirect' => null,
    ]);

    $user = User::factory()->create(['github_username' => null]);
    $token = mobileAccountApiToken($user);

    $this->withToken($token)
        ->getJson(route('api.v1.account.github.show'))
        ->assertOk()
        ->assertJsonPath('eligible', false)
        ->assertJsonPath('eligibility_reason', 'provider_unavailable');

    $this->withToken($token)
        ->postJson(route('api.v1.account.github.handoff.store'))
        ->assertServiceUnavailable();

    config([
        'services.github.client_id' => 'client-id',
        'services.github.client_secret' => 'client-secret',
        'services.github.redirect' => 'https://pinkary.test/profile/connect/github/update',
    ]);

    $user->forceFill(['github_username' => 'connected-user'])->save();
    auth()->forgetGuards();

    $this->withToken($token)
        ->getJson(route('api.v1.account.github.show'))
        ->assertOk()
        ->assertJsonPath('connected', true)
        ->assertJsonPath('github_username', 'connected-user')
        ->assertJsonPath('eligible', false)
        ->assertJsonPath('eligibility_reason', 'already_connected');

    $this->withToken($token)
        ->postJson(route('api.v1.account.github.handoff.store'))
        ->assertConflict();
});

test('GitHub handoff status expires and a connected account can disconnect', function (): void {
    Http::fake([
        'api.github.com/graphql' => Http::response(['data' => ['user' => [
            'sponsorshipForViewerAsSponsorable' => [],
        ]]]),
    ]);

    $user = User::factory()->create(['github_username' => 'connected-user']);
    $token = mobileAccountApiToken($user);
    $handoffId = (string) Illuminate\Support\Str::uuid();

    DB::table('mobile_github_handoffs')->insert([
        'id' => $handoffId,
        'user_id' => $user->id,
        'token_hash' => hash('sha256', 'expired-token'),
        'status' => 'started',
        'expires_at' => now()->subMinute(),
        'created_at' => now()->subMinutes(11),
        'updated_at' => now()->subMinutes(11),
    ]);

    $this->withToken($token)
        ->getJson(route('api.v1.account.github.handoff.show', $handoffId))
        ->assertOk()
        ->assertJsonPath('status', 'expired');

    $this->withToken($token)
        ->deleteJson(route('api.v1.account.github.destroy'))
        ->assertNoContent();

    expect($user->fresh()->github_username)->toBeNull();
});

test('GitHub handoff status rejects an invalid expiry value', function (): void {
    $user = User::factory()->create();
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('where')->twice()->andReturnSelf();
    $query->shouldReceive('first')->once()->andReturn((object) [
        'status' => 'pending',
        'expires_at' => new stdClass(),
    ]);
    DB::shouldReceive('table')->once()->with('mobile_github_handoffs')->andReturn($query);

    expect(fn (): Illuminate\Http\JsonResponse => new GitHubController()->__invoke($user, 'handoff-id'))
        ->toThrow(HttpException::class);
});
