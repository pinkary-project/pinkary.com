<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    $question = Question::factory()
        ->hasLikes(2)
        ->hasAnswer([
            'content' => 'By modifying the likes in the database :-)',
            'created_at' => now()->subDays(7),
        ])
        ->create([
            'content' => 'How did you manage to get on the trending list, tomloprod?',
            'from_id' => $user->id,
            'to_id' => $user->id,
            'created_at' => now()->subDays(7),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('will render questions without likes or comments posted now', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'created_at' => now()->subDays(12),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('do not render questions older than 7 days', function () {
    $user = User::factory()->create();

    $question = Question::factory()
        ->hasLikes(1)
        ->create([
            'from_id' => $user->id,
            'to_id' => $user->id,
            'created_at' => now()->subDays(8),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(0);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
