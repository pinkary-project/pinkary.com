<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;

test('to array', function () {
    $question = Link::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toBe([
        'id',
        'description',
        'url',
        'user_id',
        'created_at',
        'updated_at',
        'click_count',
        'is_visible',
    ]);
});

test('relations', function () {
    $link = Link::factory()->create();

    expect($link->user)->toBeInstanceOf(User::class);
});
