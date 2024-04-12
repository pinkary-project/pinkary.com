<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionFeedStrategies\TrendingFeedStrategy;
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

    $builder = (new TrendingFeedStrategy())->getBuilder();

    expect($builder->count())->toBe(1);
});

it('do not render questions without likes', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'created_at' => now()->subHours(12),
    ]);

    $builder = (new TrendingFeedStrategy())->getBuilder();

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

    $builder = (new TrendingFeedStrategy())->getBuilder();

    expect($builder->count())->toBe(0);
});

it('getBuilder returns Eloquent\Builder instance', function () {
    $builder = (new TrendingFeedStrategy())->getBuilder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
