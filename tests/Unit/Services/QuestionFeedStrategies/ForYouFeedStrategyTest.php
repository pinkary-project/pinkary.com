<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionFeedStrategies\ForYouFeedStrategy;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer',
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer 2',
        'is_reported' => false,
    ]);

    $builder = (new ForYouFeedStrategy($likerUser))->getBuilder();

    expect($builder->count())->toBe(2);
});

it('do not render questions without answer', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer',
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'is_reported' => false,
        'answer' => null,
    ]);

    $builder = (new ForYouFeedStrategy($likerUser))->getBuilder();

    expect($builder->count())->toBe(1);
});

it('do not render reported questions', function () {
    $likerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $questionWithLike = Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer',
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $likerUser->id,
        'question_id' => $questionWithLike->id,
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer 2',
        'is_reported' => true,
    ]);

    $builder = (new ForYouFeedStrategy($likerUser))->getBuilder();

    expect($builder->count())->toBe(1);
});

it('getBuilder returns Eloquent\Builder instance', function () {
    $builder = (new ForYouFeedStrategy(User::factory()->create()))->getBuilder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
