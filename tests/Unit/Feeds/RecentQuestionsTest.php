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

it('render roots of latest comments or roots without comments', function () {
    $root = Question::factory()->create(['answer' => 'root question']);
    $rootWithoutComments = Question::factory()->create(['answer' => 'root question without comments']);

    $this->travel(1)->seconds();

    Question::factory()->create([
        'answer' => 'child question',
        'root_id' => $root->id,
    ]);

    $builder = (new RecentQuestionsFeed())->builder();

    expect($builder->get()->map(fn ($root) => $root->root_id ?: $root->id)->all())
        ->toBe([$root->id, $rootWithoutComments->id]);
});

it('render roots in correct order', function () {

    // create 5 roots
    $roots = Question::factory(5)
        ->sequence(
            ['answer' => 'root 1'],
            ['answer' => 'root 2'],
            ['answer' => 'root 3'],
            ['answer' => 'root 4'],
            ['answer' => 'root 5'],
        )->create();

    // create a child for each root
    $roots->each(function ($root) {

        $this->travel(1)->seconds();

        Question::factory()->create([
            'answer' => 'child question',
            'root_id' => $root->id,
            'parent_id' => $root->id,
        ]);
    });

    $roots->load('children');

    // create a root without descendants
    $this->travel(1)->seconds();
    $rootWithoutDescendants = Question::factory()->create(['answer' => 'root without descendants']);

    // create a child for each child of even roots
    $roots->filter(fn ($root, $key) => ($key + 1) % 2 === 0) // even
        ->each(function ($root) {
            $this->travel(1)->seconds();

            Question::factory()->create([
                'answer' => 'child question',
                'parent_id' => $root->children->first()->id,
                'root_id' => $root->id,
            ]);
        });

    $builder = (new RecentQuestionsFeed())->builder();

    // final output needs to be root without descendants divided odds and evens in descending order
    expect($builder->get()->map(fn ($root) => $root->root_id ?: $root->id)->all())
        ->toBe([
            $roots->where('answer', 'root 4')->first()->id,
            $roots->where('answer', 'root 2')->first()->id,
            $rootWithoutDescendants->id,
            $roots->where('answer', 'root 5')->first()->id,
            $roots->where('answer', 'root 3')->first()->id,
            $roots->where('answer', 'root 1')->first()->id,
        ]);
});
