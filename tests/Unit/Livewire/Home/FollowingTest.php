<?php

declare(strict_types=1);

use App\Livewire\Home\QuestionsFollowing;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('/for-you redirects to /following', function () {
    $response = $this->get('/for-you');

    $response->assertRedirect('/following');
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
