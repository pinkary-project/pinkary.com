<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'created_at' => now()->subHours(12),
    ]);

    Like::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->count())->toBe(1);
});

it('do not render questions without likes', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'created_at' => now()->subHours(12),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->count())->toBe(0);
});

it('do not render questions older than 12 hours', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'created_at' => now()->subHours(13),
    ]);

    Like::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->count())->toBe(0);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
