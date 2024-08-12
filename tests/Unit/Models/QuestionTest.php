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
        'anonymously',
        'is_reported',
        'created_at',
        'updated_at',
        'pinned',
        'is_ignored',
        'views',
        'parent_id',
        'is_update',
    ]);
});

test('content', function () {
    $question = Question::factory()->create([
        'content' => 'Hello https://example.com, how are you? https://example.com',
    ])->fresh();

    expect($question->content)->toBe('Hello <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
});

test('relations', function () {
    $question = Question::factory()->create();

    $question->likes()->saveMany(Like::factory()->count(3)->make());

    expect($question->from)->toBeInstanceOf(User::class)
        ->and($question->to)->toBeInstanceOf(User::class)
        ->and($question->likes)->each->toBeInstanceOf(Like::class);
});

test('mentions', function () {
    User::factory()->create(['username' => 'firstuser']);
    User::factory()->create(['username' => 'seconduser']);

    $question = Question::factory()
        ->hasAnswer([
            'content' => 'I am doing fine, @seconduser!',
        ])
        ->create([
            'content' => 'Hello @firstuser! How are you doing?',
        ]);

    expect($question->mentions()->count())->toBe(2)
        ->and($question->mentions()->first()->username)->toBe('firstuser')
        ->and($question->mentions()->last()->username)->toBe('seconduser');
});

test('mentions when its an update', function () {
    User::factory()->create(['username' => 'firstuser']);
    User::factory()->create(['username' => 'seconduser']);

    $question = Question::factory()->create([
        'content' => 'Hello @firstuser! How are you doing?',
        'is_update' => true,
    ]);

    expect($question->mentions()->count())->toBe(1)
        ->and($question->mentions()->first()->username)->toBe('firstuser');
});

test('increment views', function () {
    $question = Question::factory()
        ->hasAnswer()
        ->create([
            'content' => 'Hello',
            'views' => 0,
        ]);

    Question::incrementViews([$question->id]);

    expect($question->fresh()->views)->toBe(1);
});

test('does not increment views without answer', function () {
    $question = Question::factory()->create(['views' => 0]);

    Question::incrementViews([$question->id]);

    expect($question->fresh()->views)->toBe(0);
});
