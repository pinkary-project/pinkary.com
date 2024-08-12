<?php

declare(strict_types=1);

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;

test('to array', function () {
    $answer = Answer::factory()->create()->fresh();

    expect(array_keys($answer->toArray()))->toBe([
        'id',
        'content',
        'question_id',
        'created_at',
        'updated_at',
    ]);
});

test('content', function () {
    $question = Question::factory()->create([
        'content' => 'Hello, how are you?',
    ])->fresh();

    expect($question->content)->toBe('Hello, how are you?');
});

test('relations', function () {
    $question = Question::factory()
        ->hasAnswer([
            'content' => 'I am doing fine!',
        ])
        ->create();

    $question->answer->load('owner', 'question');

    expect($question->answer->owner)->toBeInstanceOf(User::class)
        ->and($question->answer->question)->toBeInstanceOf(Question::class);
});
