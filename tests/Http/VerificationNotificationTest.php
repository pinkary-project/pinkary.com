<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('sends verification notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect('/');

    Notification::assertSentTo(
        $user,
        VerifyEmail::class,
        function (VerifyEmail $notification, array $channels) use ($user): bool {
            expect($channels)->toBe(['mail'])
                ->and($notification->toMail($user)->greeting)->toBe('Hello, '.$user->name.'!');

            return true;
        },
    );
});

test('does not send verification notification if email is verified', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect(route('profile.show', [
            'username' => $user->username,
        ]));

    Notification::assertNothingSent();
});

test('an API user can request the standard email verification notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.account.email-verification.notification'), [], $headers)
        ->assertAccepted()
        ->assertJsonPath('verified', false);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('an API user cannot request another verification notification after verification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.account.email-verification.notification'), [], $headers)
        ->assertAccepted()
        ->assertJsonPath('verified', true);

    Notification::assertNothingSent();
});
