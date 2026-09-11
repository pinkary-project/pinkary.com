<?php

declare(strict_types=1);

use App\Jobs\SendEmailVerification;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('queues verification notification', function (): void {
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect('/');

    Queue::assertPushed(
        SendEmailVerification::class,
        fn (SendEmailVerification $job): bool => true,
    );
});

test('sends verification notification from the queue', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    new SendEmailVerification($user)->handle();

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
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect(route('profile.show', [
            'username' => $user->username,
        ]));

    Queue::assertNothingPushed();
});
