<?php

declare(strict_types=1);

use App\Mail\PendingNotifications;
use App\Models\Question;
use App\Models\User;

test('envelope', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $mail = new PendingNotifications($user);

    $envelope = $mail->envelope();

    expect($envelope->subject)
        ->toBe('🌸 Pinkary: You Have 1 Pending Question! - '.now()->format('F j, Y'));
});

test('content', function () {
    $user = User::factory()->create();

    $mail = new PendingNotifications($user);

    foreach ([
        '# Hello, '.$user->name.'!',
        "We've noticed you have 0 pending questions. You can answer them by clicking the button below.",
        'If you no longer wish to receive these emails, you can change your "Mail Preference Time" in your [profile settings](https://pinkary.com/profile).',
    ] as $line) {
        $mail->assertSeeInText($line);
    }
});
