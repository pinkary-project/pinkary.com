<?php

declare(strict_types=1);

use App\Livewire\Notifications\Index;
use App\Models\Question;
use App\Models\User;

test('guest', function () {
    $response = $this->get(route('notifications.index'));

    $response->assertRedirect(route('login'));
});

test('auth', function () {
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

    $questionC = Question::factory()->create([
        'to_id' => $userB->id,
        'from_id' => $userA->id,
        'content' => 'Question content 3',
    ]);

    $questionC->update([
        'answer' => 'Answer content',
    ]);

    $response = $this->actingAs($userA)
        ->get(route('notifications.index'))
        ->assertStatus(200);

    $response->assertOk()
        ->assertSee('Notifications')
        ->assertSeeLivewire(Index::class);
});
