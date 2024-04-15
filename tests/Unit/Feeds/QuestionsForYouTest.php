<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function ($user) {

    $builder = (new QuestionsForYouFeed($user))->builder();

    $result = $builder->get();
    expect($result->count())->toBe(2);
})->with('user-in-for-you');

it('render questions which has answer', function ($user) {

    $builder = (new QuestionsForYouFeed($user))->builder();

    $result = $builder->get();
    $result->each(function ($question) {
        expect($question->answer)->toBe('Some answer');
    });
})->with('user-in-for-you');

it('render questions which has not been reported', function ($user) {

    $builder = (new QuestionsForYouFeed($user))->builder();

    $result = $builder->get();
    $result->each(function ($question) {
        expect($question->is_reported)->toBe(false);
    });
})->with('user-in-for-you');

it('render questions which has not been ignored', function ($user) {

    $builder = (new QuestionsForYouFeed($user))->builder();

    $result = $builder->get();
    $result->each(function ($question) {
        expect($question->is_ignored)->toBe(false);
    });
})->with('user-in-for-you');

dataset('user-in-for-you', function () {
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

    return $user;
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsForYouFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
