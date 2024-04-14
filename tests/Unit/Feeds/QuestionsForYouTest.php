<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $user = User::factory()->create();

    $inspirationalUser = User::factory()
        ->has(Question::factory()
            ->hasLikes(1, ['user_id' => $user->id])
            ->state(['answer' => 'yes']),
            'questionsReceived')
        ->create();

    Question::factory(5)
        ->sequence(
            ['answer' => null],
            ['is_reported' => true],
            ['is_ignored' => true],
            ['answer' => 'Some answer'],
            ['answer' => 'Some answer']
        )
        ->hasLikes(1, ['user_id' => $inspirationalUser->id])
        ->create();

    $builder = (new QuestionsForYouFeed($user))->builder();

    $result = $builder->get();
    expect($result->count())->toBe(2);
    $result->each(function ($question) {
        expect($question->answer)->toBe('Some answer');
        expect($question->is_reported)->toBeFalse();
        expect($question->is_ignored)->toBeFalse();
    });
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsForYouFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
