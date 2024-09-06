<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_ignored' => false,
        'is_reported' => false,
    ]);

    Like::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
    ]);

    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder->count())->toBe(1);
});

it('do not render ignored questions', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_ignored' => true,
        'is_reported' => false,
    ]);

    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder->count())->toBe(0);
});

it('do not render reported questions', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_ignored' => false,
        'is_reported' => true,
    ]);

    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder->count())->toBe(0);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});

it('can filter questions to those related to a hashtag name', function () {
    $questionWithHashtag = Question::factory()->create(['answer' => 'question 1 with a #hashtag']);

    Question::factory()->create(['answer' => 'question 2 without hashtags']);

    $builder = (new RecentQuestionsFeed('hashtag'))->builder();

    expect($builder->get()->pluck('id')->all())
        ->toBe([$questionWithHashtag->id]);
});
