<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    Question::factory()
        ->hasLikes(2)
        ->create([
            'content' => 'How did you manage to get on the trending list, tomloprod?',
            'answer' => 'By modifying the likes in the database :-)',
            'from_id' => $user->id,
            'to_id' => $user->id,
            'answer_created_at' => now()->subDays(7)->addMinute(),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('will render questions without likes or comments posted now', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now(),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('do not render questions older than 7 days', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(8),
    ]);

    Like::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(0);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
