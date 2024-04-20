<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;

test('to array', function () {
    $question = Question::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toBe([
        'id',
        'from_id',
        'to_id',
        'content',
        'answer',
        'answered_at',
        'anonymously',
        'is_reported',
        'created_at',
        'updated_at',
        'pinned',
        'is_ignored',
        'views',
    ]);
});

test('content', function () {
    $question = Question::factory()->create([
        'content' => 'Hello https://example.com, how are you? https://example.com',
    ])->fresh();

    expect($question->content)->toBe('Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
});

test('relations', function () {
    $question = Question::factory()->create();

    $question->likes()->saveMany(Like::factory()->count(3)->make());

    expect($question->from)->toBeInstanceOf(User::class)
        ->and($question->to)->toBeInstanceOf(User::class)
        ->and($question->likes)->each->toBeInstanceOf(Like::class);
});

