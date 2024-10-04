<?php

declare(strict_types=1);

use App\Livewire\Questions\Index;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertOk();
});

test('render with wrong user id', function () {
    $component = Livewire::test(Index::class, [
        'userId' => 123,
    ]);
})->throws(ModelNotFoundException::class);

test('only renders questions with answers if user is not auth user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $questions = Question::factory(3)->create([
        'from_id' => $userA->id,
        'to_id' => $userB->id,
    ]);

    $component = Livewire::actingAs($userA)->test(Index::class, [
        'userId' => $userB->id,
    ]);

    foreach ($questions as $question) {
        $component->assertSee($question->content);
    }

    $question = Question::factory()->create([
        'from_id' => $userA->id,
        'to_id' => $userB->id,
        'answer' => null,
        'answer_created_at' => null,
    ]);

    $component->dispatch('question.created');

    $component->assertDontSee($question->content);

    $question->update([
        'answer' => 'Hello World',
        'answer_created_at' => now(),
    ]);

    $component->dispatch('question.updated');

    $component->assertSee($question->content);
});

test('do not render reported questions', function () {
    $user = User::factory()->create([]);

    $questions = Question::factory(3)->create([
        'from_id' => $user->id,
        'is_reported' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    foreach ($questions as $question) {
        $component->assertDontSee($question->content);
    }
});

test('ignore', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertSee($question->content);

    $component->dispatch('question.ignore', $question->id);

    $component->assertDontSee($question->content);

    expect($question->fresh()->is_ignored)->toBeTrue();
});

test('ignore auth', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $question = Question::factory()->create([
        'from_id' => $userA->id,
        'to_id' => $userB->id,
    ]);

    $component = Livewire::actingAs($userA)->test(Index::class, [
        'userId' => $userB->id,
    ]);

    $component->dispatch('question.ignore', $question->id);

    $component->assertStatus(403);

    expect($question->fresh()->is_ignored)->not->toBeTrue();
});

test('load more', function () {
    $user = User::factory()->create();

    $questions = Question::factory(120)->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});

test('pinned question is displayed at the top', function () {
    $user = User::factory()->create();

    $pinnedQuestion = Question::factory()->create([
        'pinned' => true,
        'to_id' => $user->id,
    ]);

    $otherQuestions = Question::factory()->count(5)->create(['to_id' => $user->id, 'answer_created_at' => now()]);

    $component = Livewire::actingAs($user)->test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertSeeInOrder([
        $pinnedQuestion->content,
        ...$otherQuestions->reverse()->map->content->toArray(),
    ]);
});

it('renders the threads in the right order', function () {
    $anotherUser = User::factory()->create();
    $user = User::factory()->create();

    $answerForAnotherUser = 'another user question';
    $answerForUser = 'user question';

    $questionForAnotherUser = Question::factory()
        ->create(['answer' => $answerForAnotherUser, 'to_id' => $anotherUser->id]);

    // comment on another user's question by the user
    Question::factory()
        ->sharedUpdate()
        ->create([
            'answer' => 'comment on another user question',
            'root_id' => $questionForAnotherUser->id,
            'parent_id' => $questionForAnotherUser->id,
            'to_id' => $user->id,
            'from_id' => $user->id,
        ]);

    $questions = Question::factory()
        ->count(4)
        ->sequence(fn (Sequence $sequence) => ['answer' => $answerForUser.' '.$sequence->index + 1])
        ->create(['to_id' => $user->id]);

    // create a child for each following user's question
    $questions->each(function (Question $question) {

        $this->travel(1)->seconds();

        Question::factory()->sharedUpdate()->create([
            'answer' => '1st child question for '.$question->answer,
            'root_id' => $question->id,
            'parent_id' => $question->id,
            'to_id' => $question->to_id,
            'from_id' => $question->to_id,
        ]);
    });

    $questions->load('children');

    // create a child for each child of even roots
    $questions->filter(fn (Question $question, int $key): bool => ($key + 1) % 2 === 0) // evens
        ->each(function (Question $question): void {
            $this->travel(1)->seconds();

            Question::factory()->sharedUpdate()->create([
                'answer' => '2nd nested child question for '.$question->answer,
                'parent_id' => $question->children->first()->id,
                'root_id' => $question->id,
                'to_id' => $question->to_id,
                'from_id' => $question->to_id,
            ]);
        });

    $this->travel(1)->seconds();

    // pinned question
    $pinnedQuestion = Question::factory()->create([
        'pinned' => true,
        'to_id' => $user->id,
        'answer' => 'pinned question',
    ]);

    $this->travel(1)->seconds();

    // comment pinned question
    Question::factory()
        ->sharedUpdate()
        ->create([
            'answer' => 'comment on pinned question',
            'root_id' => $pinnedQuestion->id,
            'parent_id' => $pinnedQuestion->id,
            'to_id' => $user->id,
            'from_id' => $user->id,
        ]);

    $this->travel(1)->seconds();

    // 3rd nested child question for user's 2nd question, it's 1st child should be missing in the feed
    Question::factory()->sharedUpdate()->create([
        'answer' => '3rd nested child question for '."$answerForUser 2",
        'root_id' => $questions->where('answer', "$answerForUser 2")->first()->id,
        'parent_id' => Question::where('answer', '2nd nested child question for '."$answerForUser 2")->first()->id,
        'to_id' => $user->id,
        'from_id' => $user->id,
    ]);

    $this->travel(1)->seconds();

    // 3rd nested child question for user's 4th question from another user should be missing in the feed
    Question::factory()->sharedUpdate()->create([
        'answer' => '3rd nested child question for '."$answerForUser 4",
        'root_id' => $questions->where('answer', "$answerForUser 4")->first()->id,
        'parent_id' => Question::where('answer', '2nd nested child question for '."$answerForUser 4")->first()->id,
        'to_id' => $anotherUser->id,
        'from_id' => $anotherUser->id,
    ]);

    $component = Livewire::test(Index::class, [
        'userId' => $user->id,
    ]);

    $component->assertSeeInOrder([
        'Pinned',
        'pinned question',
        'user question 2',
        '2nd nested child question for user question 2',
        '3rd nested child question for user question 2',
        'pinned question',
        'comment on pinned question',
        'user question 4',
        '1st child question for user question 4',
        '2nd nested child question for user question 4',
        'user question 3',
        '1st child question for user question 3',
        'user question 1',
        '1st child question for user question 1',
    ]);

    $component->assertDontSee($answerForAnotherUser);
    $component->assertDontSee('comment on another user question');
    $component->assertDontSee('1st child question for user question 2');
    $component->assertDontSee('3rd nested child question for user question 4');
});
