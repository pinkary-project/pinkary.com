<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;

test('to array', function () {
    $question = Like::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toBe([
        'id',
        'user_id',
        'question_id',
        'created_at',
        'updated_at',
    ]);
});

test('relations', function () {
    $like = Like::factory()->create();

    expect($like->user)->toBeInstanceOf(User::class)
        ->and($like->question)->toBeInstanceOf(Question::class);
});
