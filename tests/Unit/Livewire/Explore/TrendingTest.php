<?php

declare(strict_types=1);

use App\Livewire\Explore\TrendingQuestions;
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
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $question->likes()->saveMany(Like::factory()->count(10)->make());

    $component = Livewire::actingAs($user)->test(TrendingQuestions::class);

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

    $component = Livewire::actingAs($user)->test(TrendingQuestions::class);

    $component
        ->assertSee('There is no trending questions right now')
        ->assertDontSee($questionContent);
});
