<?php

declare(strict_types=1);

use App\Models\Bookmark;
use App\Models\Question;
use App\Models\User;

test('to array', function () {
    $question = Bookmark::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toBe([
        'id',
        'user_id',
        'question_id',
        'created_at',
        'updated_at',
    ]);
});

test('relations', function () {
    $bookmark = Bookmark::factory()->create();

    expect($bookmark->user)->toBeInstanceOf(User::class)
        ->and($bookmark->question)->toBeInstanceOf(Question::class);
});
