<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\Database\Eloquent\Builder;

it('renders questions with right conditions', function () {
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

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->count())->toBe(1);

    expect($builder->first()->answer)->toBe('Answer 2');
});

it('does not render questions without answer', function () {
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
        'answer' => 'Answer 2',
    ]);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'is_reported' => false,
        'answer' => null,
    ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->count())->toBe(1);

    expect($builder->first()->answer)->toBe('Answer 2');
});

it('does not render reported questions', function () {
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

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer 3',
        'is_reported' => false,
    ]);

    $builder = (new QuestionsForYouFeed($likerUser))->builder();

    expect($builder->count())->toBe(1);

    expect($builder->first()->answer)->toBe('Answer 3');
});

it('returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsForYouFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
