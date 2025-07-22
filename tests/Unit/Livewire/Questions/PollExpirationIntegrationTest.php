<?php

declare(strict_types=1);

use App\Livewire\Questions\PollVoting;
use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('poll expiration integration test', function (): void {
    $user = User::factory()->create();

    $activeQuestion = Question::factory()->create([
        'is_poll' => true,
        'poll_expires_at' => now()->addHour(),
    ]);
    $activePollOption = PollOption::factory()->create([
        'question_id' => $activeQuestion->id,
        'text' => 'Active Option',
    ]);

    $expiredQuestion = Question::factory()->create([
        'is_poll' => true,
        'poll_expires_at' => now()->subHour(),
    ]);
    $expiredPollOption = PollOption::factory()->create([
        'question_id' => $expiredQuestion->id,
        'text' => 'Expired Option',
    ]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $activeQuestion->id])
        ->call('vote', $activePollOption->id)
        ->assertHasNoErrors();

    expect($activePollOption->fresh()->votes_count)->toBe(1);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $expiredQuestion->id])
        ->call('vote', $expiredPollOption->id)
        ->assertHasErrors(['poll']);

    expect($expiredPollOption->fresh()->votes_count)->toBe(0);

    $activeComponent = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $activeQuestion->id]);
    $activeComponent->assertDontSee('Poll expired');

    $expiredComponent = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $expiredQuestion->id]);
    $expiredComponent->assertSee('Poll expired');
});
