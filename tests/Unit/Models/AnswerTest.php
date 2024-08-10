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
        'content' => 'Hello https://example.com, how are you? https://example.com',
    ])->fresh();

    expect($question->content)->toBe('Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
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
