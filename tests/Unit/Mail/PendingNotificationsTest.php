<?php

declare(strict_types=1);

use App\Mail\PendingNotifications;
use App\Models\SuppressedEmail;
use App\Models\User;
use Spatie\MailcoachMailer\Exceptions\EmailNotValid;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Exception\HttpTransportException;

test('envelope', function (): void {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user, 1);

    $envelope = $mail->envelope();

    expect($envelope->subject)
        ->toBe('🌸 Pinkary: You Have 1 Notification! - '.now()->format('F j, Y'));
});

test('content', function (): void {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user, 1);

    foreach ([
        '# Hello, '.$user->name.'!',
        "We've noticed you have 1 notification. You can view notifications by clicking the button below.",
        'If you no longer wish to receive these emails, you can change your "Mail Preference Time" in your [profile settings]('.config('app.url').'/profile).',
    ] as $line) {
        $mail->assertSeeInText($line);
    }
});

test('records suppression on mailcoach rejection', function (): void {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user, 3);
    $mail->failed(new HttpTransportException(
        'Unable to send an email (code 406).',
        new MockResponse('{}', ['http_code' => 406])
    ));

    expect(SuppressedEmail::query()->where('email', $user->email)->exists())->toBeTrue();
});

test('records suppression on invalid email', function (): void {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user, 3);
    $mail->failed(EmailNotValid::make('invalid'));

    expect(SuppressedEmail::query()->where('email', $user->email)->exists())->toBeTrue();
});

test('ignores transient failures', function (): void {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user, 3);
    $mail->failed(new HttpTransportException(
        'Mailcoach server error.',
        new MockResponse('{}', ['http_code' => 500])
    ));
    $mail->failed(new Exception('Connection lost.'));

    expect(SuppressedEmail::query()->where('email', $user->email)->exists())->toBeFalse();
});
