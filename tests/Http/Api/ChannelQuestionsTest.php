<?php

declare(strict_types=1);

use App\Models\Channel;
use App\Models\Question;
use App\Models\User;

test('a guest can paginate answered posts in a channel', function (): void {
    $channel = Channel::factory()->create();
    $now = now();

    $oldest = Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => 'Oldest answer.',
        'updated_at' => $now->copy()->subMinutes(2),
    ]);
    $middle = Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => 'Middle answer.',
        'updated_at' => $now->copy()->subMinute(),
    ]);
    $newest = Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => 'Newest answer.',
        'updated_at' => $now->copy(),
    ]);
    Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => null,
        'updated_at' => $now->copy()->addMinute(),
    ]);

    $parameters = ['channel' => $channel->slug, 'per_page' => 2];
    $firstPage = $this->getJson(route('api.v1.channels.questions.index', $parameters))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newest->id)
        ->assertJsonPath('data.1.id', $middle->id);

    expect($firstPage->json('links.next'))->toBeString()->toContain('page=2');

    $this->getJson(route('api.v1.channels.questions.index', [...$parameters, 'page' => 2]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $oldest->id)
        ->assertJsonPath('links.next', null);
});

test('guests and members cannot read posts in an admin-only channel', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Announcements',
        'slug' => Channel::ADMIN_ONLY_SLUGS[0],
    ]);
    Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => 'An announcement.',
    ]);

    $this->getJson(route('api.v1.channels.questions.index', $channel->slug))
        ->assertNotFound();

    $member = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$member->createToken('test')->plainTextToken];

    auth()->forgetGuards();

    $this->getJson(route('api.v1.channels.questions.index', $channel->slug), $headers)
        ->assertNotFound();
});

test('an admin can read posts in an admin-only channel', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Announcements',
        'slug' => Channel::ADMIN_ONLY_SLUGS[0],
    ]);
    $post = Question::factory()->create([
        'channel_id' => $channel->id,
        'answer' => 'An announcement.',
    ]);
    $admin = User::factory()->admin()->create();
    $headers = ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.channels.questions.index', $channel->slug), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $post->id)
        ->assertJsonPath('data.0.answer', 'An announcement.');
});
