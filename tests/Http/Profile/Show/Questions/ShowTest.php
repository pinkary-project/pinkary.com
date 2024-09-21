<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Livewire\Questions\Show;
use App\Models\Question;
use App\Models\User;

test('guest', function () {
    $question = Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $response = $this->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertOk()->assertSee([
        $question->content,
        'This is the answer',
    ]);

    $response->assertSeeLivewire(Show::class);
});

test('auth', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $response = $this->actingAs($user)->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertSee([
        $question->content,
        'This is the answer',
    ]);

    $response->assertSeeLivewire(Show::class);
    $response->assertSeeLivewire(Create::class);
});

test('reported question is not visible', function () {
    $question = Question::factory()->create([
        'is_reported' => true,
    ]);

    $response = $this->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertStatus(403);
});

test('question without answer is not visible for other users', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'answer' => null,
    ]);

    $response = $this->actingAs($user)->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertStatus(403);

    $response = $this->actingAs($question->to)->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertOk()
        ->assertSee($question->content);
});

test('question is not visible for other usernames on the url', function () {
    $question = Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $response = $this->get(route('questions.show', [
        'username' => 'wrongusername',
        'question' => $question->id,
    ]));

    $response->assertStatus(404);
});

test('it shows the parent questions', function () {
    $user = User::factory()->create();

    $parent1 = Question::factory()->create();
    $parent2 = Question::factory()->create([
        'parent_id' => $parent1->id,
    ]);
    $parent3 = Question::factory()->create([
        'parent_id' => $parent2->id,
    ]);
    $question = Question::factory()->create([
        'parent_id' => $parent3->id,
    ]);

    $response = $this->actingAs($user)->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertOk()
        ->assertViewHas('parentQuestions', function (array $parentQuestions) use ($parent1, $parent2, $parent3) {
            return $parentQuestions[0]->id === $parent3->id
                && $parentQuestions[1]->id === $parent2->id
                && $parentQuestions[2]->id === $parent1->id;
        });
});
