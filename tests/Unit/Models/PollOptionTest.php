<?php

declare(strict_types=1);

use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use Carbon\CarbonInterface;

test('to array', function () {
    $pollOption = PollOption::factory()->create()->fresh();

    expect(array_keys($pollOption->toArray()))->toBe([
        'id',
        'question_id',
        'text',
        'votes_count',
        'created_at',
        'updated_at',
    ]);
});

test('relations', function () {
    $question = Question::factory()->create(['is_poll' => true]);

    $pollOption = PollOption::factory()
        ->for($question)
        ->create();

    $pollVote = PollVote::factory()
        ->for($pollOption)
        ->create();

    expect($pollOption->question)->toBeInstanceOf(Question::class)
        ->and($pollOption->question->id)->toBe($question->id)
        ->and($pollOption->votes)->toHaveCount(1)
        ->and($pollOption->votes->first())->toBeInstanceOf(PollVote::class);
});

test('fillable attributes', function () {
    $question = Question::factory()->create(['is_poll' => true]);

    $pollOption = PollOption::create([
        'question_id' => $question->id,
        'text' => 'Option 1',
        'votes_count' => 5,
    ]);

    expect($pollOption->question_id)->toBe($question->id)
        ->and($pollOption->text)->toBe('Option 1')
        ->and($pollOption->votes_count)->toBe(5);
});

test('casts', function () {
    $pollOption = PollOption::factory()->create([
        'votes_count' => '10',
    ]);

    expect($pollOption->votes_count)->toBeInt()
        ->and($pollOption->created_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($pollOption->updated_at)->toBeInstanceOf(CarbonInterface::class);
});
