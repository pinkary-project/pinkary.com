<?php

declare(strict_types=1);

use App\Console\Commands\SendReminderEmailsCommand;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('sends daily emails', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'mail_preference_time' => 'weekly',
    ]);

    User::factory(2)->create([
        'mail_preference_time' => 'never',
    ]);

    $questioner = User::factory()->create([
        'mail_preference_time' => 'never',
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

    $this->artisan(SendReminderEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueuedCount(5);
});

test('sends weekly emails', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'mail_preference_time' => 'weekly',
    ]);

    User::factory(2)->create([
        'mail_preference_time' => 'never',
    ]);

    $questioner = User::factory()->create([
        'mail_preference_time' => 'never',
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

    $this->artisan(SendReminderEmailsCommand::class, ['--weekly' => true])
        ->assertExitCode(0);

    Mail::assertQueuedCount(2);
});
