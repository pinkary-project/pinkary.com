<?php

declare(strict_types=1);

use App\Livewire\PeopleToFollow;
use App\Livewire\Questions\Create;
use App\Livewire\Questions\Show;
use App\Models\Question;
use App\Models\User;

test('guest', function (): void {
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

test('auth', function (): void {
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

test('answer translate action is visible before bookmarks', function (): void {
    $question = Question::factory()->create([
        'content' => 'Question content',
        'answer' => 'Answer content',
    ]);

    $response = $this->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $answerTranslateUrl = e('https://translate.google.com/?sl=auto&tl=en&text='.urlencode((string) $question->sharable_answer));

    $response->assertOk()
        ->assertSeeInOrder([
            $answerTranslateUrl,
            'data-is-bookmarked="false"',
        ], false);
});

test('reported question is not visible', function (): void {
    $question = Question::factory()->create([
        'is_reported' => true,
    ]);

    $response = $this->get(route('questions.show', [
        'username' => $question->to->username,
        'question' => $question->id,
    ]));

    $response->assertStatus(403);
});

test('question without answer is not visible for other users', function (): void {
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

test('question is not visible for other usernames on the url', function (): void {
    $question = Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $response = $this->get(route('questions.show', [
        'username' => 'wrongusername',
        'question' => $question->id,
    ]));

    $response->assertStatus(404);
});

test('it shows the parent questions', function (): void {
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
        ->assertViewHas('parentQuestions', fn (array $parentQuestions): bool => $parentQuestions[0]->id === $parent3->id
            && $parentQuestions[1]->id === $parent2->id
            && $parentQuestions[2]->id === $parent1->id);
});

test('it shows question-context suggestions in the people to follow rail', function (): void {
    $postUser = User::factory()->create();
    $currentParticipant = User::factory()->create();
    $recentInteractionUser = User::factory()->create(['name' => 'Question Rail Interaction']);

    Question::factory()->create([
        'from_id' => $recentInteractionUser->id,
        'to_id' => $postUser->id,
        'answer' => 'Question answer',
        'updated_at' => now()->subMinutes(5),
    ]);

    $question = Question::factory()->create([
        'from_id' => $currentParticipant->id,
        'to_id' => $postUser->id,
        'updated_at' => now(),
    ]);

    $response = $this->get(route('questions.show', [
        'username' => $postUser->username,
        'question' => $question->id,
    ]));

    $response->assertOk()
        ->assertSeeLivewire(PeopleToFollow::class)
        ->assertSee('Question Rail Interaction');
});
