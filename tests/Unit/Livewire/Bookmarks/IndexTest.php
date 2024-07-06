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
