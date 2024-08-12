<?php

declare(strict_types=1);

use App\Livewire\Notifications\Index;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays notifications', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $questionA = Question::factory()->create([
        'to_id' => $userA->id,
        'from_id' => $userB->id,
        'content' => 'Question content 1',
    ]);

    $questionB = Question::factory()->create([
        'to_id' => $userA->id,
        'from_id' => $userB->id,
        'content' => 'Question content 2',
    ]);

    $questionC = Question::factory()
        ->hasAnswer()
        ->create([
            'to_id' => $userB->id,
            'from_id' => $userA->id,
            'content' => 'Question content 3',
        ]);

    $userA->notify(new QuestionAnswered($questionC));

    /** @var Testable $component */
    $component = Livewire::actingAs($userA->fresh())->test(Index::class);

    $component
        ->assertSee([
            'Question content 1',
            'Question content 2',
            'Question content 3',
        ]);
});
