<?php

declare(strict_types=1);

use App\Console\Commands\SendUnreadNotificationEmailsCommand;
use App\Enums\UserMailPreference;
use App\Mail\PendingNotifications;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('sends daily emails', function (): void {
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

it('sends weekly emails', function (): void {
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

it('mails getting notification count', function (): void {
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

    Mail::assertQueued(PendingNotifications::class, fn (PendingNotifications $mail): bool => $mail->pendingNotificationsCount === 3);
});

it('skips suppressed emails', function (): void {
    $suppressed = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Never,
    ]);

    foreach ([$suppressed, $recipient] as $user) {
        $questioner->questionsSent()->create([
            'to_id' => $user->id,
            'content' => 'What is the meaning of life?',
        ]);
    }

    App\Models\SuppressedEmail::factory()->create([
        'email' => $suppressed->email,
    ]);

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, 1);
    Mail::assertQueued(PendingNotifications::class, fn (PendingNotifications $mail): bool => $mail->user->is($recipient));
});

it('queues an email for due unread notifications and leaves other notifications untouched', function (): void {
    $this->travelTo('2026-10-01 12:00:00');

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $eligibleQuestion = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Eligible for a reminder.',
    ]);

    $eligibleNotification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $eligibleQuestion->id)
        ->firstOrFail();

    $recentQuestion = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Already reminded recently.',
    ]);

    $recentNotification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $recentQuestion->id)
        ->firstOrFail();

    $readQuestion = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Already read.',
    ]);

    $readNotification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $readQuestion->id)
        ->firstOrFail();

    $recipient->notifications()->whereKey($eligibleNotification->id)->update([
        'email_reminded_at' => now()->subDays(30),
    ]);

    $recipient->notifications()->whereKey($recentNotification->id)->update([
        'email_reminded_at' => now()->subDays(29),
    ]);

    $recipient->notifications()->whereKey($readNotification->id)->update([
        'read_at' => now(),
    ]);

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, fn (PendingNotifications $mail): bool => $mail->user->is($recipient)
        && $mail->pendingNotificationsCount === 2
        && $mail->notificationIds === [$eligibleNotification->id]
        && $mail->afterCommit === false);

    $this->assertDatabaseHas('notifications', [
        'id' => $eligibleNotification->id,
        'email_reminded_at' => now(),
    ]);

    $this->assertDatabaseHas('notifications', [
        'id' => $recentNotification->id,
        'email_reminded_at' => now()->subDays(29),
    ]);
});

it('sends the same unread notification again after 30 days', function (): void {
    $this->travelTo('2026-10-01 12:00:00');

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $question = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Please answer me.',
    ]);

    $notification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $question->id)
        ->firstOrFail();

    Mail::fake();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    $this->travelTo('2026-10-30 12:00:00');

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, 1);

    $this->travelTo('2026-10-31 12:00:00');

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    Mail::assertQueued(PendingNotifications::class, 2);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'email_reminded_at' => now(),
    ]);
});

it('allows a reminder to be retried after the queued email fails', function (): void {
    $this->travelTo('2026-10-01 12:00:00.123456');

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $question = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Please answer me.',
    ]);

    $notification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $question->id)
        ->firstOrFail();

    $remindedAt = now();
    $recipient->notifications()->whereKey($notification->id)->update(['email_reminded_at' => $remindedAt]);

    new PendingNotifications($recipient, 1, [$notification->id], $remindedAt)
        ->failed(new RuntimeException('Mail delivery failed.'));

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'email_reminded_at' => null,
    ]);
});

it('stores the reminder and queued email in the same database transaction', function (): void {
    config([
        'queue.default' => 'database',
        'queue.connections.database.driver' => 'database',
        'queue.connections.database.connection' => null,
        'queue.connections.database.table' => 'jobs',
        'queue.connections.database.after_commit' => true,
    ]);

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $question = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Please answer me.',
    ]);

    $notification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $question->id)
        ->firstOrFail();

    $this->artisan(SendUnreadNotificationEmailsCommand::class)
        ->assertExitCode(0);

    $this->assertDatabaseCount('jobs', 1);
    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'email_reminded_at' => now()->startOfSecond(),
    ]);
});

it('rolls back the reminder when database queue dispatch fails', function (): void {
    config([
        'queue.default' => 'database',
        'queue.connections.database.driver' => 'database',
        'queue.connections.database.connection' => null,
        'queue.connections.database.table' => 'missing_jobs',
    ]);

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $question = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Please answer me.',
    ]);

    $notification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $question->id)
        ->firstOrFail();

    expect(fn () => $this->artisan(SendUnreadNotificationEmailsCommand::class))
        ->toThrow(Illuminate\Database\QueryException::class);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'email_reminded_at' => null,
    ]);
});

it('keeps a newer reminder when an older queued email later fails', function (): void {
    $this->travelTo('2026-10-01 12:00:00.123456');

    $recipient = User::factory()->create([
        'mail_preference_time' => UserMailPreference::Daily,
    ]);

    $questioner = User::factory()->create();

    $question = $questioner->questionsSent()->create([
        'to_id' => $recipient->id,
        'content' => 'Please answer me.',
    ]);

    $notification = $recipient->notifications()
        ->whereJsonContains('data->question_id', $question->id)
        ->firstOrFail();

    $olderReminderAt = now();
    $recipient->notifications()->whereKey($notification->id)->update(['email_reminded_at' => $olderReminderAt]);

    $this->travel(30)->days();
    $newerReminderAt = now()->startOfSecond();
    $recipient->notifications()->whereKey($notification->id)->update(['email_reminded_at' => $newerReminderAt]);

    new PendingNotifications($recipient, 1, [$notification->id], $olderReminderAt)
        ->failed(new RuntimeException('An older mail delivery failed.'));

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'email_reminded_at' => $newerReminderAt,
    ]);
});
