<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('question can have poll expiration date', function (): void {
    $user = User::factory()->create();
    $expirationDate = now()->addDays(3);

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => $expirationDate,
    ]);

    expect($question->poll_expires_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
    expect($question->poll_expires_at->format('Y-m-d H:i:s'))->toBe($expirationDate->format('Y-m-d H:i:s'));
});

test('question poll expiration date is nullable', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => false,
        'poll_expires_at' => null,
    ]);

    expect($question->poll_expires_at)->toBeNull();
});

test('question can determine if poll is expired', function (): void {
    $user = User::factory()->create();

    // Expired poll
    $expiredQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->subDay(),
    ]);

    // Active poll
    $activeQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->addDay(),
    ]);

    // Non-poll question
    $nonPollQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => false,
        'poll_expires_at' => null,
    ]);

    expect($expiredQuestion->poll_expires_at->isPast())->toBeTrue();
    expect($activeQuestion->poll_expires_at->isFuture())->toBeTrue();
    expect($nonPollQuestion->poll_expires_at)->toBeNull();
});

test('question poll expiration date is cast to datetime', function (): void {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => '2025-07-25 12:00:00',
    ]);

    expect($question->poll_expires_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
    expect($question->poll_expires_at->format('Y-m-d H:i:s'))->toBe('2025-07-25 12:00:00');
});

test('isPollExpired returns true for expired polls', function (): void {
    $user = User::factory()->create();

    $expiredQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->subDay(),
    ]);

    expect($expiredQuestion->isPollExpired())->toBeTrue();
});

test('isPollExpired returns false for active polls', function (): void {
    $user = User::factory()->create();

    $activeQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->addDay(),
    ]);

    expect($activeQuestion->isPollExpired())->toBeFalse();
});

test('isPollExpired returns false for non-poll questions', function (): void {
    $user = User::factory()->create();

    $nonPollQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => false,
        'poll_expires_at' => null,
    ]);

    expect($nonPollQuestion->isPollExpired())->toBeFalse();
});

test('isPollExpired returns false for polls without expiration date', function (): void {
    $user = User::factory()->create();

    $pollWithoutExpiration = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => null,
    ]);

    expect($pollWithoutExpiration->isPollExpired())->toBeFalse();
});

test('getPollTimeRemaining returns null for expired polls', function (): void {
    $user = User::factory()->create();

    $expiredQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->subDay(),
    ]);

    expect($expiredQuestion->getPollTimeRemaining())->toBeNull();
});

test('getPollTimeRemaining returns null for non-poll questions', function (): void {
    $user = User::factory()->create();

    $nonPollQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => false,
        'poll_expires_at' => null,
    ]);

    expect($nonPollQuestion->getPollTimeRemaining())->toBeNull();
});

test('getPollTimeRemaining returns null for polls without expiration date', function (): void {
    $user = User::factory()->create();

    $pollWithoutExpiration = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => null,
    ]);

    expect($pollWithoutExpiration->getPollTimeRemaining())->toBeNull();
});

test('getPollTimeRemaining returns time remaining for active polls', function (): void {
    $user = User::factory()->create();

    $activeQuestion = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_poll' => true,
        'poll_expires_at' => now()->addDays(2),
    ]);

    $timeRemaining = $activeQuestion->getPollTimeRemaining();

    expect($timeRemaining)->toBeString();
    expect($timeRemaining)->toContain('day');
    expect($timeRemaining)->toMatch('/\d+\s+day/');
});
