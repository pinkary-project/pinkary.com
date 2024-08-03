<?php

declare(strict_types=1);

use App\Livewire\Bookmarks\Index;
use App\Models\Bookmark;
use App\Models\Question;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays bookmarks', function () {
    $user = User::factory()->create();

    Question::factory()
        ->has(Bookmark::factory()->for($user))
        ->create([
            'content' => 'Question content 1',
        ]);

    Question::factory()
        ->has(Bookmark::factory()->for($user))
        ->create([
            'content' => 'Question content 2',
        ]);

    Question::factory()
        ->has(Bookmark::factory()->for($user))
        ->create([
            'content' => 'Question content 3',
            'answer' => 'Answer content 3',
        ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $component
        ->assertSee([
            'Question content 1',
            'Question content 2',
            'Question content 3',
        ]);
});

test('refresh', function () {
    $user = User::factory()->create();

    Question::factory()
        ->has(Bookmark::factory()->for($user))
        ->create([
            'content' => 'Some Question',
        ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $component->assertSee('Some Question');

    $component->dispatch('question.unbookmarked');

    $component->assertDontSee('Some Question');
});

test('load more', function () {
    $user = User::factory()->create();

    Question::factory(120)->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $component->call('loadMore');
    $component->assertSet('perPage', 10);

    $component->call('loadMore');
    $component->assertSet('perPage', 15);

    foreach (range(1, 25) as $i) {
        $component->call('loadMore');
    }

    $component->assertSet('perPage', 100);
});
