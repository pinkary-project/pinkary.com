<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\Question;

test('to array', function () {
    $question = Hashtag::factory()->create();

    expect(array_keys($question->toArray()))->toContain(
        'id',
        'name',
        'created_at',
        'updated_at',
    )->toHaveCount(4);
});

test('relations', function () {
    $hashtag = Hashtag::factory()
        ->hasQuestions(1)
        ->create();

    expect($hashtag->questions)->each->toBeInstanceOf(Question::class);
});
