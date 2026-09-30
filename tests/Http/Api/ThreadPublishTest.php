<?php

declare(strict_types=1);

use App\Models\Channel;
use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->headers = ['Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken];
});

test('a follow-up post can carry its own poll', function (): void {
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'thread_posts' => ['Follow-up with a poll.'],
        'thread_polls' => [
            ['options' => ['  Yes  ', 'No'], 'duration' => 3],
        ],
    ], $this->headers)->assertCreated()
        ->assertJsonCount(2, 'data');

    $followUp = Question::where('parent_id', '!=', null)->sole();

    expect($followUp->poll_expires_at)->not->toBeNull()
        ->and($followUp->poll_expires_at->between(now()->addDays(2), now()->addDays(4)))->toBeTrue()
        ->and(PollOption::where('question_id', $followUp->id)->orderBy('id')->pluck('text')->all())
        ->toBe(['Yes', 'No']);
});

test('a thread poll needs two to four real options and a sane duration', function (): void {
    foreach ([
        'one option' => ['options' => ['Only one'], 'duration' => 3],
        'a blank option' => ['options' => ['Yes', '   '], 'duration' => 3],
        'five options' => ['options' => ['a', 'b', 'c', 'd', 'e'], 'duration' => 3],
        'a duration past the limit' => ['options' => ['Yes', 'No'], 'duration' => 8],
        'a zero duration' => ['options' => ['Yes', 'No'], 'duration' => 0],
    ] as $poll) {
        $this->postJson(route('api.v1.questions.store'), [
            'content' => 'Main post.',
            'thread_posts' => ['Follow-up.'],
            'thread_polls' => [$poll],
        ], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Each thread poll needs 2 to 4 non-empty options and a duration of 1 to 7 days.']);
    }

    $this->assertDatabaseCount('questions', 0);
});

test('a thread poll with no duration given closes after a day', function (): void {
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'thread_posts' => ['Follow-up.'],
        'thread_polls' => [
            ['options' => ['Yes', 'No']],
        ],
    ], $this->headers)->assertCreated();

    $followUp = Question::where('parent_id', '!=', null)->sole();

    expect($followUp->poll_expires_at)->toBeBetween(now()->addHours(23), now()->addHours(25));
});

test('a blank poll option is refused by validation rather than by the action', function (): void {
    // poll_options.* is required, so validation rejects this before the
    // action's own guard runs.
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'poll_options' => ['   ', 'Real option'],
        'poll_duration' => 3,
    ], $this->headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('poll_options.0');

    $this->assertDatabaseCount('questions', 0);
});

test('a channel name that slugs to nothing publishes without a channel', function (): void {
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'channel_name' => '---',
    ], $this->headers)->assertCreated();

    // Passes validation, but slugs to nothing.
    expect(Question::sole()->channel_id)->toBeNull()
        ->and(Channel::query()->count())->toBe(0);
});

test('a non-admin cannot publish into an admin only channel by name', function (): void {
    expect($this->user->isAdmin())->toBeFalse();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'channel_name' => 'Announcements',
    ], $this->headers)->assertCreated();

    // Dropped, not refused, matching the web composer's staging.
    expect(Question::sole()->channel_id)->toBeNull()
        ->and(Channel::query()->count())->toBe(0);
});

test('a non-admin cannot publish into an existing admin only channel', function (): void {
    $channel = Channel::factory()->create(['name' => 'Announcements', 'slug' => 'announcements']);

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'channel_id' => $channel->id,
    ], $this->headers)->assertCreated();

    expect(Question::sole()->channel_id)->toBeNull();
});

test('a non-admin can publish into a channel they name', function (): void {
    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Main post.',
        'channel_name' => 'Laravel',
    ], $this->headers)->assertCreated();

    $channel = Channel::sole();

    expect($channel->slug)->toBe('laravel')
        ->and(Question::sole()->channel_id)->toBe($channel->id);
});
