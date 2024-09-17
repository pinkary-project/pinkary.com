<?php

declare(strict_types=1);

use App\Livewire\Home\TrendingQuestions;
use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders trending questions', function () {
    $user = User::factory()->create();

    $questionContent = 'This is a trending question!';

    $question = Question::factory()->hasLikes(2)->create([
        'content' => $questionContent,
        'answer' => 'This is the answer',
        'answer_created_at' => now()->subDays(7)->addMinute(),
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    Like::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
    ]);

    $component = Livewire::test(TrendingQuestions::class);

    $component
        ->assertDontSee('There is no trending questions right now')
        ->assertSee($questionContent);
});

test('do not renders trending questions', function () {
    $user = User::factory()->create();

    $questionContent = 'Is this a trending question?';

    Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'No',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $component = Livewire::test(TrendingQuestions::class);

    $component
        ->assertSee('There is no trending questions right now')
        ->assertDontSee($questionContent);
});

test('renders trending questions order by trending score', function () {
    $this->travelTo($date = now());

    // 0 likes, 0 comments, just posted
    // (proves the algo works with zeroes)
    Question::factory()
        ->create([
            'content' => 'trending question 1',
            'answer_created_at' => $date,
            'views' => 20,
        ]); // score = .00001157

    // 0 likes, 0 comments, posted 10 minutes ago
    Question::factory()
        ->create([
            'content' => 'trending question 2',
            'answer_created_at' => $date->subMinutes(10),
            'views' => 20,
        ]); // score = .00001149

    // 1 like, 0 comments, posted 10 minutes ago
    Question::factory()
        ->hasLikes(1)
        ->create([
            'content' => 'trending question 3',
            'answer_created_at' => $date->subMinutes(10),
            'views' => 100,
        ]); // score = .00002298

    // 0 likes, 1 comment, posted 10 minutes ago (same score as above)
    Question::factory()
        ->afterCreating(fn (Question $question) => Question::factory()->create([
            'parent_id' => $question->id,
            'content' => 'comment on question 4',
            'answer_created_at' => $date->subMinutes(10),
        ]))
        ->create([
            'content' => 'trending question 4',
            'answer_created_at' => $date->subMinutes(10),
            'views' => 100,
        ]); // score = .00002298

    // 1 like, 0 comments, posted 11 minutes ago (just below question 3)
    Question::factory()
        ->hasLikes(1)
        ->create([
            'content' => 'trending question 5',
            'answer_created_at' => $date->subMinutes(11),
            'views' => 500,
        ]); // score = .00002297

    // 1 like, 1 comment, posted 15 minutes ago
    Question::factory()
        ->hasLikes(1)
        ->afterCreating(fn (Question $question) => Question::factory()->create([
            'parent_id' => $question->id,
            'content' => 'comment on question 6',
            'answer_created_at' => $date->subMinutes(15),
        ]))
        ->create([
            'content' => 'trending question 6',
            'answer_created_at' => $date->subMinutes(15),
            'views' => 700,
        ]); // score = .0000459

    // 20 likes, 0 comments, posted a day ago
    Question::factory()
        ->hasLikes(20)
        ->create([
            'content' => 'trending question 7',
            'answer_created_at' => $date->subDay(),
            'views' => 500,
        ]); // score = .0001215

    // Prove we limit to max days since posted
    Question::factory()
        ->hasLikes(50)
        ->create([
            'content' => 'trending question 8',
            'answer_created_at' => $date->subDays(8),
            'views' => 500,
        ]); // score = .00006559 (would otherwise be trending...)

    $component = Livewire::test(TrendingQuestions::class, ['perPage' => 10]);

    $component->assertSeeInOrder([
        'trending question 7',
        'trending question 6',
        'trending question 3',
        'trending question 4',
        'trending question 5',
        'trending question 1',
        'trending question 2',
    ]);
    $component->assertDontSee('trending question 8');
});
