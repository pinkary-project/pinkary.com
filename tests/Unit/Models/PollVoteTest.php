<?php

declare(strict_types=1);

use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use App\Models\User;
use Carbon\CarbonInterface;

test('to array', function () {
    $pollVote = PollVote::factory()->create()->fresh();

    expect(array_keys($pollVote->toArray()))->toBe([
        'id',
        'user_id',
        'poll_option_id',
        'created_at',
        'updated_at',
    ]);
});

test('relations', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['poll_expires_at' => now()->addDays(1)]);
    $pollOption = PollOption::factory()->for($question)->create();

    $pollVote = PollVote::factory()
        ->for($user)
        ->for($pollOption)
        ->create();

    expect($pollVote->user)->toBeInstanceOf(User::class)
        ->and($pollVote->user->id)->toBe($user->id)
        ->and($pollVote->pollOption)->toBeInstanceOf(PollOption::class)
        ->and($pollVote->pollOption->id)->toBe($pollOption->id);
});

test('fillable attributes', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['poll_expires_at' => now()->addDays(1)]);
    $pollOption = PollOption::factory()->for($question)->create();

    $pollVote = PollVote::create([
        'user_id' => $user->id,
        'poll_option_id' => $pollOption->id,
    ]);

    expect($pollVote->user_id)->toBe($user->id)
        ->and($pollVote->poll_option_id)->toBe($pollOption->id);
});

test('casts', function () {
    $pollVote = PollVote::factory()->create();

    expect($pollVote->user_id)->toBeInt()
        ->and($pollVote->poll_option_id)->toBeInt()
        ->and($pollVote->created_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($pollVote->updated_at)->toBeInstanceOf(CarbonInterface::class);
});

test('unique vote constraint', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create(['poll_expires_at' => now()->addDays(1)]);
    $pollOption = PollOption::factory()->for($question)->create();

    // First vote should succeed
    $pollVote1 = PollVote::create([
        'user_id' => $user->id,
        'poll_option_id' => $pollOption->id,
    ]);

    expect($pollVote1)->toBeInstanceOf(PollVote::class);

    // Second vote for same user and option should fail
    expect(fn () => PollVote::create([
        'user_id' => $user->id,
        'poll_option_id' => $pollOption->id,
    ]))->toThrow(Exception::class);
});
