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

    $question = Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'This is the answer',
        'answer_created_at' => now()->subDays(7),
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

test('renders trending questions orderby trending score', function () {
    // (likes * 0.5 + views * 0.2) / (minutes since answered + 1) = trending score

    Question::factory()
        ->hasLikes(5)
        ->create([
            'content' => 'trending question 1',
            'answered_at' => now()->subMinutes(10),
            'views' => 20,
        ]); // score = (5 * 0.5 + 20 * 0.2) / (10 + 1) = 0.59

    Question::factory()
        ->hasLikes(5)
        ->create([
            'content' => 'trending question 2',
            'answered_at' => now()->subMinutes(20),
            'views' => 20,
        ]); // score = (5 * 0.5 + 20 * 0.2) / (20 + 1) = 0.30

    Question::factory()
        ->hasLikes(15)
        ->create([
            'content' => 'trending question 3',
            'answered_at' => now()->subMinutes(20),
            'views' => 100,
        ]); // score = (15 * 0.5 + 100 * 0.2) / (20 + 1) = 1.30

    Question::factory()
        ->hasLikes(20)
        ->create([
            'content' => 'trending question 4',
            'answered_at' => now()->subMinutes(20),
            'views' => 100,
        ]); // score = (20 * 0.5 + 100 * 0.2) / (20 + 1) = 1.42

    Question::factory()
        ->hasLikes(50)
        ->create([
            'content' => 'trending question 5',
            'answered_at' => now()->subMinutes(30),
            'views' => 500,
        ]); // score = (50 * 0.5 + 500 * 0.2) / (30 + 1) = 4.03

    Question::factory()
        ->hasLikes(50)
        ->create([
            'content' => 'trending question 6',
            'answered_at' => now()->subMinutes(30),
            'views' => 700,
        ]); // score = (50 * 0.5 + 700 * 0.2) / (30 + 1) = 5.32

    $component = Livewire::test(TrendingQuestions::class);
    $component->assertSeeInOrder([
        'trending question 6',
        'trending question 5',
        'trending question 4',
        'trending question 3',
        'trending question 1',
        'trending question 2',
    ]);
});
