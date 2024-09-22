<?php

declare(strict_types=1);

use App\Livewire\Home\Feed;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders questions with answers', function () {
    Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $component = Livewire::test(Feed::class);

    $component->assertSee('This is the answer')
        ->assertDontSee('There are no questions to show.');
});

test('do not renders questions without answers', function () {
    $user = User::factory()->create();

    Question::factory()->create([
        'answer' => null,
    ]);

    $component = Livewire::actingAs($user)->test(Feed::class);

    $component->assertSee('There are no questions to show.');
});

test('do not renders ignored questions', function () {
    Question::factory()->create([
        'answer' => 'This is the answer',
        'is_ignored' => true,
    ]);

    $component = Livewire::test(Feed::class);

    $component->assertSee('There are no questions to show.');
});

test('ignore', function () {
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(Feed::class);

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

    $component = Livewire::actingAs($userA)->test(Feed::class);

    $component->dispatch('question.ignore', $question->id);

    $component->assertStatus(403);

    expect($question->fresh()->is_ignored)->not->toBeTrue();
});

test('load more', function () {
    $user = User::factory()->create();

    $questions = Question::factory(120)->create();

    $component = Livewire::actingAs($user)->test(Feed::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});

it('displays questions from users I am following', function () {
    $user = User::factory()->create();
    $notFollowing = User::factory()->create();
    $following = User::factory()->create();

    $user->following()->attach($following);

    Question::factory()->create([
        'content' => 'Do you like star wars?',
        'answer' => 'May the force be with you!',
        'to_id' => $following,
    ]);

    $component = Livewire::actingAs($user)->test(Feed::class);

    $component->assertSee('Do you like star wars?');
});

test('refresh', function () {
    $component = Livewire::test(Feed::class);

    Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $component->assertSee('There are no questions to show.')
        ->assertDontSee('This is the answer');

    $component->dispatch('question.created');

    $component->assertSee('This is the answer')
        ->assertDontSee('There are no questions to show.');
});

it('renders the threads in the right order', function () {

    // create 4 roots
    $roots = Question::factory()
        ->forEachSequence(
            ['answer' => 'root 1'],
            ['answer' => 'root 2'],
            ['answer' => 'root 3'],
            ['answer' => 'root 4'],
        )->create();

    // create a child for each root
    $roots->each(function (Question $root) {

        $this->travel(1)->seconds();

        Question::factory()->create([
            'answer' => '1st child question for '.str($root->answer)->snake(),
            'root_id' => $root->id,
            'parent_id' => $root->id,
        ]);
    });

    $roots->load('children');

    // create a root without descendants
    $this->travel(1)->seconds();
    Question::factory()->create(['answer' => 'root without descendants']);

    // create a child for each child of even roots
    $roots->filter(fn (Question $root, int $key) => ($key + 1) % 2 === 0) // evens
        ->each(function (Question $root) {
            $this->travel(1)->seconds();

            Question::factory()->create([
                'answer' => '2nd nested child question for '.str($root->answer)->snake(),
                'parent_id' => $root->children->first()->id,
                'root_id' => $root->id,
            ]);
        });

    $this->travel(1)->seconds();

    // 3rd nested child question for root 2 so child needs to be missing from feed
    Question::factory()->create([
        'answer' => '3rd nested child question for root2',
        'root_id' => $roots->where('answer', 'root 2')->first()->id,
        'parent_id' => Question::where('answer', '2nd nested child question for root2')->first()->id,
    ]);

    $component = Livewire::test(Feed::class);

    // final output needs to be root without descendants divided odds and evens in descending order
    // in this case, root 2 has a child that has a child so it should not be in the feed
    // and as we added 3rd nested child question for root2, it should be first in the feed
    $component->assertSeeInOrder([
        'root 2',
        '2nd nested child question for root2',
        '3rd nested child question for root2',
        'root 4',
        '1st child question for root4',
        '2nd nested child question for root4',
        'root without descendants',
        'root 3',
        '1st child question for root3',
        'root 1',
        '1st child question for root1',
    ]);

    $component->assertDontSee('1st child question for root2');
});
