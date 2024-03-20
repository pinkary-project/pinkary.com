<?php

declare(strict_types=1);

use App\Livewire\Home;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders questions with answers', function () {
    Question::factory()->create([
        'answer' => 'This is the answer',
    ]);

    $component = Livewire::test(Home::class);

    $component->assertSee('This is the answer')
        ->assertDontSee('There are no questions to show.');
});

test('do not renders questions without answers', function () {
    Question::factory()->create([
        'answer' => null,
    ]);

    $component = Livewire::test(Home::class);

    $component->assertSee('There are no questions to show.');
});

test('load more', function () {
    $user = User::factory()->create();

    $questions = Question::factory(120)->create();

    $component = Livewire::actingAs($user)->test(Home::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});
