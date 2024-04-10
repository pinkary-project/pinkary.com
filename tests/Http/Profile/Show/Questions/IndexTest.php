<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Feed;
use App\Models\Question;
use App\Models\User;

it('dispatches job to check & increment question views on profile', function () {
    Queue::fake();
    $user = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(Feed::class, compact('user'));

    $component->call('ignore', $question->id);

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'is_ignored' => true,
        'to_id' => $user->id,
        'views' => 0,
    ]);
    Queue::assertPushed(IncrementViews::class);
});
