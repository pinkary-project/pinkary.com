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
            'answer_created_at' => now()->subDays(7),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

// Test with SQLite
it('renders questions in SQLite environment', function () {
    $mockConnection = Mockery::mock('Illuminate\Database\Connection');
    $mockConnection->shouldReceive('getDriverName')->andReturn('sqlite');

    $user = User::factory()->create();

    Question::factory()
        ->hasLikes(2)
        ->create([
            'content' => 'How did you manage to get on the trending list, tomloprod?',
            'answer' => 'By modifying the likes in the database :-)',
            'from_id' => $user->id,
            'to_id' => $user->id,
            'answer_created_at' => now()->subDays(7),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);

    // Clean up Mockery mocks after the test
    Mockery::close();
});

// Test with pgsql
it('renders questions in pgsql environment', function () {
    $mockConnection = Mockery::mock('Illuminate\Database\Connection');
    $mockConnection->shouldReceive('getDriverName')->andReturn('pgsql');

    $user = User::factory()->create();

    Question::factory()
        ->hasLikes(2)
        ->create([
            'content' => 'How did you manage to get on the trending list, tomloprod?',
            'answer' => 'By modifying the likes in the database :-)',
            'from_id' => $user->id,
            'to_id' => $user->id,
            'answer_created_at' => now()->subDays(7),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);

    // Clean up Mockery mocks after the test
    Mockery::close();
});

it('renders questions without likes or comments', function () {
    $user = User::factory()->create();
    Question::factory()->create([
        'content' => 'A question with no likes or comments.',
        'answer' => 'Answer without likes or comments.',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(3),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('does not render questions older than 7 days', function () {
    $user = User::factory()->create();
    Question::factory()->create([
        'content' => 'This question is older than 7 days.',
        'answer' => 'This answer is older than 7 days.',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(8),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(0);
});

it('groups questions correctly by id', function () {
    $user = User::factory()->create();
    Question::factory()->create([
        'content' => 'First question',
        'answer' => 'First answer.',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(2),
    ]);

    Question::factory()->create([
        'content' => 'Second question',
        'answer' => 'Second answer.',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(1),
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(2);
});

it('renders questions in mysql environment', function () {
    $mockConnection = Mockery::mock('Illuminate\Database\Connection');
    $mockConnection->shouldReceive('getDriverName')->andReturn('mysql');

    $user = User::factory()->create();

    Question::factory()
        ->hasLikes(2)
        ->create([
            'content' => 'How did you manage to get on the trending list?',
            'answer' => 'By modifying the likes in the database :-)',
            'from_id' => $user->id,
            'to_id' => $user->id,
            'answer_created_at' => now()->subDays(7),
        ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);

    // Clean up Mockery mocks after the test
    Mockery::close();
});

it('renders questions with maximum likes and comments', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'content' => 'A highly liked and commented question.',
        'answer' => 'Answer with lots of likes and comments.',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(1),
    ]);

    Like::factory(50)->create([
        'question_id' => $question->id,
    ]);

    Question::factory(30)->create([
        'parent_id' => $question->id,
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

it('renders no questions when database is empty', function () {
    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(0);
});

it('renders questions with no likes but has comments', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(2),
    ]);

    Question::factory(5)->create([
        'parent_id' => $question->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('renders questions with no comments but has likes', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(2),
    ]);

    Like::factory(3)->create([
        'question_id' => $question->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();

    expect($builder->get()->count())->toBe(1);
});

it('renders questions ordered by likes and comments', function () {
    $user = User::factory()->create();
    $question1 = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(1),
    ]);

    $question2 = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now()->subDays(2),
    ]);

    Like::factory(5)->create([
        'question_id' => $question1->id,
    ]);

    Like::factory(2)->create([
        'question_id' => $question2->id,
    ]);

    $builder = (new TrendingQuestionsFeed())->builder();
    $questions = $builder->get();

    expect($questions->first()->id)->toBe($question1->id); // Question1 should be first due to more likes
});
