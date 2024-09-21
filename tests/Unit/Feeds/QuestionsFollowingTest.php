<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsFollowingFeed;
use Illuminate\Database\Eloquent\Builder;

it('render questions with right conditions', function () {
    $followerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $question1 = Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer',
        'is_reported' => false,
    ]);

    $followerUser->following()->attach($userTo->id);

    $question2 = Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer 2',
        'is_reported' => false,
    ]);

    $builder = (new QuestionsFollowingFeed($followerUser))->builder();

    expect($builder->pluck('id')->all())->toEqual([$question2->id, $question1->id]);
});

it('do not render questions without answer', function () {
    $followerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $answer = 'Answer to the question that needs to be rendered';

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => $answer,
        'is_reported' => false,
    ]);

    $followerUser->following()->attach($userTo->id);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'is_reported' => false,
        'answer' => null,
    ]);

    $builder = (new QuestionsFollowingFeed($followerUser))->builder();

    expect($builder->where('answer', $answer)->count())->toBe(1);
});

it('includes questions made to users i follow', function () {
    $user = User::factory()->create();

    $follower = User::factory()->create();

    $follower->following()->attach($user);

    Question::factory()->create([
        'to_id' => $user->id,
        'answer' => 'Answer',
    ]);

    $builder = (new QuestionsFollowingFeed($follower))->builder();

    expect($builder->count())->toBe(1);
});

it('do not render reported questions', function () {
    $followerUser = User::factory()->create();

    $userTo = User::factory()->create();

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer',
        'is_reported' => false,
    ]);

    $followerUser->following()->attach($userTo->id);

    Question::factory()->create([
        'to_id' => $userTo->id,
        'answer' => 'Answer 2',
        'is_reported' => true,
    ]);

    $builder = (new QuestionsFollowingFeed($followerUser))->builder();

    expect($builder->where('is_reported', false)->count())->toBe(1);
});

it('does not show the comments if it\'s on non following user\'s post', function () {
    $followerUser = User::factory()->create();

    $userTo = User::factory()->create();

    $followerUser->following()->attach($userTo->id);

    $question = Question::factory()->create([
        'answer' => 'Answer',
    ]);

    Question::factory()->create([
        'parent_id' => $question->id,
        'answer' => 'Answer 2',
        'from_id' => $userTo->id,
        'to_id' => $userTo->id,
    ]);

    $builder = (new QuestionsFollowingFeed($followerUser))->builder();

    expect($builder->count())->toBe(0);
});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsFollowingFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
