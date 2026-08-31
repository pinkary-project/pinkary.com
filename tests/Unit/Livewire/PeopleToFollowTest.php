<?php

declare(strict_types=1);

use App\Livewire\PeopleToFollow;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('renders the people to follow widget', function (): void {
    User::factory(5)
        ->hasLinks(1, fn (array $attributes, User $user): array => ['url' => "https://twitter.com/{$user->username}"])
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    Livewire::test(PeopleToFollow::class)
        ->assertStatus(200)
        ->assertSee('People to follow')
        ->assertSee('View all');
});

it('uses the current context and authenticated user when rendering recommendations', function (): void {
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

it('allows following a user', function (): void {
    $viewer = User::factory()->create();
    $target = User::factory()->create();

    Livewire::actingAs($viewer)->test(PeopleToFollow::class)
        ->call('follow', $target->id);

    expect($viewer->following()->where('user_id', $target->id)->exists())->toBeTrue();
});

it('allows unfollowing a user', function (): void {
    $viewer = User::factory()->create();
    $target = User::factory()->create();

    $viewer->following()->attach($target);

    Livewire::actingAs($viewer)->test(PeopleToFollow::class)
        ->call('unfollow', $target->id);

    expect($viewer->following()->where('user_id', $target->id)->exists())->toBeFalse();
});
