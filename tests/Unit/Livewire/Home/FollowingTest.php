<?php

declare(strict_types=1);

use App\Livewire\Home\QuestionsFollowing;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders questions with follow of authenticated user', function () {
    $user = User::factory()->create();

    $questionContent = 'This is a question with a follow from the authenticated user';

    Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'Cool question',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $authUser = User::factory()->create();

    $authUser->following()->attach($user->id);

    $component = Livewire::actingAs($authUser)->test(QuestionsFollowing::class);

    $component
        ->assertDontSee('We haven\'t found any questions that may interest you based on the activity you\'ve done on Pinkary.')
        ->assertSee($questionContent);
});

test('do not renders questions without follow of authenticated user', function () {
    $user = User::factory()->create();

    $questionContent = 'This is a question with a follow from the authenticated user';

    Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'Cool question',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $authUser = User::factory()->create();

    $component = Livewire::actingAs($authUser)->test(QuestionsFollowing::class);

    $component
        ->assertSee('found any questions that may interest you based on the activity')
        ->assertDontSee($questionContent);
});

test('load more', function () {
    $user = User::factory()->create();

    Question::factory(120)->create();

    $component = Livewire::actingAs($user)->test(QuestionsFollowing::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});

it('renders the threads in the right order', function () {

    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $authUser = User::factory()->create();

    $authUser->following()->attach($user->id);
    $authUser->following()->attach($anotherUser->id);

    $answerForFollowingUser = 'following user question';
    $answerForAnotherFollowingUser = 'another following user question';
    $answerForAuthUser = 'auth user question';
    $answerForNonFollowingUser = 'non following user post';

    $questions = Question::factory()
        ->forEachSequence(
            ['answer' => $answerForFollowingUser, 'to_id' => $user->id],
            ['answer' => $answerForAnotherFollowingUser, 'to_id' => $anotherUser->id],
            ['answer' => $answerForAuthUser, 'to_id' => $authUser->id],
            ['answer' => $answerForNonFollowingUser],
        )->create();

    // create a child for each following user's question
    $questions->each(function (Question $question) use ($answerForNonFollowingUser) {

        $this->travel(1)->seconds();

        Question::factory()->sharedUpdate()->create([
            'answer' => '1st child question for '.str($question->answer)->snake(),
            'root_id' => $question->id,
            'parent_id' => $question->id,
            'to_id' => $question->where('answer', '!=', $answerForNonFollowingUser)->inRandomOrder()->first()->to_id,
        ]);
    });

    $questions->load('children');

    // create a root without descendants
    $this->travel(1)->seconds();
    Question::factory()->sharedUpdate()->create(['answer' => 'root without descendants', 'to_id' => $user->id]); // by following user

    // create a child for each child of even roots
    $questions->filter(fn (Question $question, int $key) => ($key + 1) % 2 === 0) // evens
        ->each(function (Question $question) use ($answerForNonFollowingUser) {
            $this->travel(1)->seconds();

            Question::factory()->sharedUpdate()->create([
                'answer' => '2nd nested child question for '.str($question->answer)->snake(),
                'parent_id' => $question->children->first()->id,
                'root_id' => $question->id,
                'to_id' => $question->where('answer', '!=', $answerForNonFollowingUser)->inRandomOrder()->first()->to_id, // random following user
            ]);
        });

    $this->travel(1)->seconds();

    // 3rd nested child question for another following user question, it's 1st child should be missing in the feed
    Question::factory()->sharedUpdate()->create([
        'answer' => '3rd nested child question for '.str($answerForAnotherFollowingUser)->snake(),
        'root_id' => $questions->where('answer', $answerForAnotherFollowingUser)->first()->id,
        'parent_id' => Question::where('answer', '2nd nested child question for '.str($answerForAnotherFollowingUser)->snake())->first()->id,
        'to_id' => $authUser->id,
    ]);

    // 3rd nested child question for non following user question from non following user should be missing in the feed
    Question::factory()->sharedUpdate()->create([
        'answer' => '3rd nested child question for '.str($answerForNonFollowingUser)->snake(),
        'root_id' => $questions->where('answer', $answerForNonFollowingUser)->first()->id,
        'parent_id' => Question::where('answer', '2nd nested child question for '.str($answerForNonFollowingUser)->snake())->first()->id,
    ]);

    $component = Livewire::actingAs($authUser)->test(QuestionsFollowing::class);

    // final output needs to be root without descendants divided odds and evens in descending order
    // 1st child question for another following user question should be missing
    // answers for non following user should be missing
    // 3rd nested child question for auth user should be missing
    $component->assertSeeInOrder([
        $answerForAnotherFollowingUser,
        '2nd nested child question for '.str($answerForAnotherFollowingUser)->snake(),
        '3rd nested child question for '.str($answerForAnotherFollowingUser)->snake(),
        '1st child question for '.str($answerForNonFollowingUser)->snake(),
        '2nd nested child question for '.str($answerForNonFollowingUser)->snake(),
        'root without descendants',
        $answerForAuthUser,
        '1st child question for '.str($answerForAuthUser)->snake(),
        $answerForFollowingUser,
        '1st child question for '.str($answerForFollowingUser)->snake(),
    ]);

    $component->assertDontSee('1st child question for '.str($answerForAnotherFollowingUser)->snake());
    $component->assertDontSee($answerForNonFollowingUser);
    $component->assertDontSee('3rd nested child question for '.str($answerForNonFollowingUser)->snake());
});
