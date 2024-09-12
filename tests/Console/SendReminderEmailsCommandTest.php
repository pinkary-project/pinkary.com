<?php

declare(strict_types=1);

use App\Console\Commands\SendUnreadNotificationEmailsCommand;
use App\Enums\UserMailPreference;
use App\Mail\PendingNotifications;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('sends daily emails', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'mail_preference_time' => UserMailPreference::Weekly,
    ]);

    User::factory(2)->create([
        'mail_preference_time' => UserMailPreference::Never,
    ]);

    $questioner = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Never,
    ]);

    User::all()->each(fn (User $user) => $questioner->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life?',
    ]));

    $questioner->questionsSent()->create([
        'to_id' => $questioner->id,
        'content' => 'Sharing updates will not create a new notification.',
    ]);

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, 5);
});

test('sends weekly emails', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'mail_preference_time' => UserMailPreference::Weekly,
    ]);

    User::factory(2)->create([
        'mail_preference_time' => UserMailPreference::Never,
    ]);

    $questioner = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Never,
    ]);

    User::all()->each(fn (User $user) => $questioner->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life?',
    ]));

    $questioner->questionsSent()->create([
        'to_id' => $questioner->id,
        'content' => 'Sharing updates will not create a new notification.',
    ]);

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class, ['--weekly' => true])
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, 2);
});

test('mails getting notification count', function () {
    $user = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $questioner->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life?',
    ]);

    $questioner->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life? ignoring?',
    ]);

    $questioner->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life? please answer me.',
    ]);

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, function (PendingNotifications $mail) {
        return $mail->pendingNotificationsCount === 3;
    });
});
