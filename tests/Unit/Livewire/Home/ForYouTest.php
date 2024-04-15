<?php

declare(strict_types=1);

use App\Livewire\Home\QuestionsForYou;
use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders questions with likes of authenticated user', function () {
    $user = User::factory()->create();

    $questionContent = 'This is a question with a like from the authenticated user';

    $question = Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'Cool question',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $like = Like::factory()->create([
        'user_id' => $user->id,
    ]);

    $question->likes()->save($like);

    $component = Livewire::actingAs($user)->test(QuestionsForYou::class);

    $component
        ->assertDontSee('We haven\'t found any questions that may interest you based on the activity you\'ve done on Pinkary.')
        ->assertSee($questionContent);
});

test('do not renders questions without likes of authenticated user', function () {
    $user = User::factory()->create();

    $questionContent = 'This is a question with a like from the authenticated user';

    Question::factory()->create([
        'content' => $questionContent,
        'answer' => 'Cool question',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)->test(QuestionsForYou::class);

    $component
        ->assertSee('found any questions that may interest you based on the activity')
        ->assertDontSee($questionContent);
});

test('load more', function () {
    $user = User::factory()->create();

    Question::factory(120)->create();

    $component = Livewire::actingAs($user)->test(QuestionsForYou::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});
