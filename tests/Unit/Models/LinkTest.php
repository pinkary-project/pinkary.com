<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;

test('to array', function (): void {
    $question = Link::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toContain(
        'id',
        'description',
        'url',
        'user_id',
        'created_at',
        'updated_at',
        'click_count',
        'is_visible',
    )->toHaveCount(8);
});

test('relations', function (): void {
    $link = Link::factory()->create();

    expect($link->user)->toBeInstanceOf(User::class);
});
