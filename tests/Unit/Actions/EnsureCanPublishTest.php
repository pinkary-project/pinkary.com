<?php

declare(strict_types=1);

use App\Actions\Questions\EnsureCanPublish;
use App\Models\Question;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

function sentPosts(User $user, int $count, string $createdAt): void
{
    Question::factory()->count($count)->create([
        'from_id' => $user->id,
        'created_at' => $createdAt,
    ]);
}

test('a user sending three posts a minute is stopped', function (): void {
    $user = User::factory()->create();
    sentPosts($user, 3, now()->toDateTimeString());

    expect(fn () => (new EnsureCanPublish)->handle($user))
        ->toThrow(HttpException::class, 'You can only send 3 questions per minute.');
});

test('a user sending thirty posts a day is stopped before the day is up', function (): void {
    $user = User::factory()->create();

    // Spread over the day so the per-minute limit is not what trips.
    sentPosts($user, 30, now()->subHours(5)->toDateTimeString());

    expect(fn () => (new EnsureCanPublish)->handle($user))
        ->toThrow(HttpException::class, 'You can only send 30 questions per day.');
});

test('the daily limit counts the posts this request is about to create', function (): void {
    $user = User::factory()->create();
    sentPosts($user, 29, now()->subHours(5)->toDateTimeString());

    // One already sent leaves room for exactly one more, so a thread that
    // would add two posts has to be refused before the write.
    (new EnsureCanPublish)->handle($user);

    expect(fn () => (new EnsureCanPublish)->handle($user, 2))
        ->toThrow(HttpException::class, 'You can only send 30 questions per day.');
});

test('a user under both limits may publish', function (): void {
    $user = User::factory()->create();
    sentPosts($user, 2, now()->toDateTimeString());

    (new EnsureCanPublish)->handle($user);

    expect(true)->toBeTrue();
});

test('local development is never rate limited', function (): void {
    $this->app->detectEnvironment(fn (): string => 'local');

    $user = User::factory()->create();
    sentPosts($user, 50, now()->toDateTimeString());

    // Otherwise a developer seeding fixtures locks themselves out.
    (new EnsureCanPublish)->handle($user);

    expect(true)->toBeTrue();
});
