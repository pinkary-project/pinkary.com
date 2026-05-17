<?php

declare(strict_types=1);

use App\Livewire\PeopleToFollow;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('renders the people to follow widget', function () {
    User::factory(5)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    Livewire::test(PeopleToFollow::class)
        ->assertStatus(200)
        ->assertSee('People to follow')
        ->assertSee('View all');
});

it('uses the current context and authenticated user when rendering recommendations', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create();
    $followedInteractedUser = User::factory()->create();
    $visibleInteractedUser = User::factory()->create();

    $viewer->following()->attach($followedInteractedUser);

    Question::factory()->create([
        'from_id' => $followedInteractedUser->id,
        'to_id' => $profileUser->id,
        'answer' => 'Followed answer',
        'updated_at' => now()->subMinutes(2),
    ]);

    Question::factory()->create([
        'from_id' => $visibleInteractedUser->id,
        'to_id' => $profileUser->id,
        'answer' => 'Visible answer',
        'updated_at' => now()->subMinute(),
    ]);

    Livewire::actingAs($viewer)->test(PeopleToFollow::class, [
        'context' => 'profile',
        'contextUserId' => $profileUser->id,
    ])
        ->assertStatus(200)
        ->assertSee($visibleInteractedUser->name)
        ->assertDontSee($followedInteractedUser->name);
});
