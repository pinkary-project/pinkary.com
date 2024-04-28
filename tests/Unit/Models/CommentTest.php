<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Question;
use App\Models\User;

it('to array', function () {
    $comment = Comment::factory()
        ->for(Question::factory()->create())
        ->create();

    expect(array_keys($comment->toArray()))->toBe([
        'id',
        'user_id',
        'question_id',
        'content',
        'is_reported',
        'created_at',
        'updated_at',
    ]);
});

test('content', function () {
    $comment = Comment::factory()
        ->for(Question::factory()->create())
        ->create([
            'content' => 'Hello https://example.com, how are you? https://example.com',
        ])->fresh();

    expect($comment->content)->toBe('Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
});

test('raw content', function () {
    $comment = Comment::factory()
        ->for(Question::factory()->create())
        ->create([
            'content' => 'Hello https://example.com, how are you? https://example.com',
        ])->fresh();

    expect($comment->raw_content)->toBe('Hello https://example.com, how are you? https://example.com');
});

test('relations', function () {
    $comment = Comment::factory()
        ->for(Question::factory()->create())
        ->create();

    expect($comment->owner)->toBeInstanceOf(User::class)
        ->and($comment->question)->toBeInstanceOf(Question::class);
});
