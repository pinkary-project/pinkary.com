<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\Tag;

uses()->group('frank');

test('to array', function () {
    $tag = Tag::factory()->create()->fresh();

    expect(array_keys($tag->toArray()))->toBe([
        'id',
        'is_trending',
        'name',
        'created_at',
        'updated_at',
    ]);
});

test('relations', function () {
    $tag = Tag::factory()->create();
    $questions = Question::factory(2)->create();
    $tag->questions()->attach($questions->pluck('id'));

    expect($tag->questions->first())->toBeInstanceOf(Question::class);
});
