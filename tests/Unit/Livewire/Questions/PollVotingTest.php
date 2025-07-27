<?php

declare(strict_types=1);

use App\Livewire\Questions\PollVoting;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('renders poll options correctly', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();

    $pollOptions = PollOption::factory()
        ->for($question)
        ->createMany([
            ['text' => 'Option 1', 'votes_count' => 5],
            ['text' => 'Option 2', 'votes_count' => 3],
            ['text' => 'Option 3', 'votes_count' => 2],
        ]);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    $component->assertSee('Option 1')
        ->assertSee('Option 2')
        ->assertSee('Option 3')
        ->assertSee('10 votes');
});

test('user can vote on poll option', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();
    $pollOption = PollOption::factory()->for($question)->create(['votes_count' => 0]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    expect(PollVote::where('user_id', $user->id)
        ->where('poll_option_id', $pollOption->id)
        ->exists())->toBeTrue();

    expect($pollOption->fresh()->votes_count)->toBe(1);
});

test('user can change their vote', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();

    $option1 = PollOption::factory()->for($question)->create(['votes_count' => 1]);
    $option2 = PollOption::factory()->for($question)->create(['votes_count' => 0]);

    PollVote::factory()->create([
        'user_id' => $user->id,
        'poll_option_id' => $option1->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $option2->id);

    expect(PollVote::where('user_id', $user->id)
        ->where('poll_option_id', $option1->id)
        ->exists())->toBeFalse();

    expect(PollVote::where('user_id', $user->id)
        ->where('poll_option_id', $option2->id)
        ->exists())->toBeTrue();

    expect($option1->fresh()->votes_count)->toBe(0);
    expect($option2->fresh()->votes_count)->toBe(1);
});

test('user can remove their vote by voting for same option', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();
    $pollOption = PollOption::factory()->for($question)->create(['votes_count' => 1]);

    PollVote::factory()->create([
        'user_id' => $user->id,
        'poll_option_id' => $pollOption->id,
    ]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    expect(PollVote::where('user_id', $user->id)
        ->where('poll_option_id', $pollOption->id)
        ->exists())->toBeFalse();

    expect($pollOption->fresh()->votes_count)->toBe(0);
});

test('guest user cannot vote', function (): void {
    $question = Question::factory()->poll()->create();
    $pollOption = PollOption::factory()->for($question)->create();

    $component = Livewire::test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    $component->assertRedirect(route('login'));

    expect(PollVote::count())->toBe(0);
});

test('displays correct vote percentages', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();

    PollOption::factory()->for($question)->create(['text' => 'Option 1', 'votes_count' => 6]);
    PollOption::factory()->for($question)->create(['text' => 'Option 2', 'votes_count' => 4]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->assertSee('60%')
        ->assertSee('40%');
});

test('shows user selected option correctly', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();

    $option1 = PollOption::factory()->for($question)->create(['text' => 'Selected']);
    $option2 = PollOption::factory()->for($question)->create(['text' => 'Not Selected']);

    PollVote::factory()->create([
        'user_id' => $user->id,
        'poll_option_id' => $option1->id,
    ]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->assertSee('Selected')
        ->assertSee('Not Selected');
});

test('prevents voting on non-existent poll option', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->poll()->create();

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    expect(fn () => $component->call('vote', 99999))
        ->toThrow(Exception::class);
});

test('prevents voting on poll option from different question', function (): void {
    $user = User::factory()->create();
    $question1 = Question::factory()->poll()->create();
    $question2 = Question::factory()->poll()->create();

    $pollOption = PollOption::factory()->for($question2)->create();

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question1->id]);

    expect(fn () => $component->call('vote', $pollOption->id))
        ->toThrow(Exception::class);
});

test('requires verified email to vote', function (): void {
    $user = User::factory()->unverified()->create();
    $question = Question::factory()->create(['poll_expires_at' => now()->addDays(1)]);
    $pollOption = PollOption::factory()->create(['question_id' => $question->id]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    expect(PollVote::where('user_id', $user->id)->exists())->toBeFalse();
});

test('cannot vote on expired poll', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->subDay(),
    ]);
    $pollOption = PollOption::factory()->create(['question_id' => $question->id]);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    $component->assertHasErrors(['poll' => 'This poll has expired and voting is no longer allowed.']);
    expect(PollVote::where('user_id', $user->id)->exists())->toBeFalse();
});

test('can vote on active poll', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->addDay(),
    ]);
    $pollOption = PollOption::factory()->create(['question_id' => $question->id]);

    Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id])
        ->call('vote', $pollOption->id);

    expect(PollVote::where('user_id', $user->id)->exists())->toBeTrue();
});

test('renders expired poll status correctly', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->subDay(),
    ]);
    PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Option 1']);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    $component->assertSee('Poll expired');
    $component->assertViewHas('isPollExpired', true);
});

test('renders active poll status correctly', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->addDay(),
    ]);
    PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Option 1']);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    $component->assertDontSee('Poll expired');
    $component->assertViewHas('isPollExpired', false);
});

test('displays time remaining for active polls', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->addDays(2),
    ]);
    PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Option 1']);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    $component->assertSee('Ends ');
    $component->assertViewHas('timeRemaining');
    $timeRemaining = $component->viewData('timeRemaining');
    expect($timeRemaining)->toBeString();
    expect($timeRemaining)->toContain('day');
});

test('does not display time remaining for expired polls', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create([
        'poll_expires_at' => now()->subDay(),
    ]);
    PollOption::factory()->create(['question_id' => $question->id, 'text' => 'Option 1']);

    $component = Livewire::actingAs($user)
        ->test(PollVoting::class, ['questionId' => $question->id]);

    $component->assertSee('Poll expired');
    $component->assertDontSee('Ends in');
    $component->assertViewHas('timeRemaining', null);
});
