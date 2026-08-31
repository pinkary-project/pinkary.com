<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function (): void {
    $response = $this->get('/forgot-password');

    $response->assertOk();
});

test('reset password link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification): true {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertOk();

        return true;
    });
});

test('reset password link ignores the host header of the request', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('https://evil.com/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): true {
        $url = $notification->toMail($user)->actionUrl;

        expect($url)
            ->toStartWith(mb_rtrim((string) config('app.url'), '/').'/reset-password/')
            ->not->toContain('evil.com');

        return true;
    });
});

test('password can be reset with valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user): true {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
});
