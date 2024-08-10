<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    $question1 = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'is_ignored' => false,
        'is_reported' => false,
        'is_update' => true,
    ]);

    $question2 = Question::factory()
        ->hasAnswer()
        ->create([
            'from_id' => User::factory()->create()->id,
            'to_id' => $user->id,
            'is_ignored' => false,
            'is_reported' => false,
            'is_update' => false,
        ]);

    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder->count())->toBe(2);
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
