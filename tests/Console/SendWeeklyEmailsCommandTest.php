<?php

declare(strict_types=1);

use App\Console\Commands\SendWeeklyEmailsCommand;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('sends weekly emails', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'mail_preference_time' => 'weekly',
    ]);

    User::factory(2)->create([
        'mail_preference_time' => 'never',
    ]);

    User::all()->each(fn (User $user) => $user->questionsSent()->create([
        'to_id' => $user->id,
        'content' => 'What is the meaning of life?',
    ]));

    Mail::fake();

    $this->artisan(SendWeeklyEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueuedCount(2);
});
