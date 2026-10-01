<?php

declare(strict_types=1);

use App\Enums\UserDefaultFeed;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

// Behaviours where the API answered differently from the web for the same
// person. Each of these was verified against the Livewire path it mirrors.

test('an omitted anonymously flag follows the user preference', function (): void {
    Notification::fake();

    $sender = User::factory()->create(['prefers_anonymous_questions' => true]);
    $receiver = User::factory()->create();

    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $id = $this->postJson(route('api.v1.questions.store'), [
        'to_username' => $receiver->username,
        'content' => 'A question.',
    ], $headers)->assertCreated()->json('data.0.id');

    // The web seeds its composer with this preference and the column
    // defaults to true. Defaulting to false meant a user who had chosen
    // "ask anonymously" published with their name attached from the app.
    expect(Question::findOrFail($id)->anonymously)->toBeTrue();
});

test('an explicit anonymously flag still wins', function (): void {
    Notification::fake();

    $sender = User::factory()->create(['prefers_anonymous_questions' => true]);
    $receiver = User::factory()->create();

    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $id = $this->postJson(route('api.v1.questions.store'), [
        'to_username' => $receiver->username,
        'content' => 'A question.',
        'anonymously' => false,
    ], $headers)->assertCreated()->json('data.0.id');

    expect(Question::findOrFail($id)->anonymously)->toBeFalse();
});

test('a user who prefers named questions gets their name', function (): void {
    Notification::fake();

    $sender = User::factory()->create(['prefers_anonymous_questions' => false]);
    $receiver = User::factory()->create();

    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $id = $this->postJson(route('api.v1.questions.store'), [
        'to_username' => $receiver->username,
        'content' => 'A question.',
    ], $headers)->assertCreated()->json('data.0.id');

    expect(Question::findOrFail($id)->anonymously)->toBeFalse();
});

test('the feed defaults to the tab the user chose', function (): void {
    // Follows nobody, so the "following" tab is empty even though there is a
    // perfectly good post to read.
    Question::factory()->create([
        'content' => 'A recent answer.',
        'answer' => 'Yes.',
        'created_at' => now()->subMinute(),
    ]);

    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Following]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    // The web redirects / to the user's default_feed, whose column default
    // is "following". Hardcoding "recent" gave the same person a different
    // home feed on the app than on the site.
    $this->getJson(route('api.v1.feed.index'), $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a user who chose recent still sees posts from people they do not follow', function (): void {
    Question::factory()->create([
        'content' => 'A recent answer.',
        'answer' => 'Yes.',
        'created_at' => now()->subMinute(),
    ]);

    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Recent]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.feed.index'), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('an explicit tab still wins over the preference', function (): void {
    Question::factory()->create([
        'content' => 'A recent answer.',
        'answer' => 'Yes.',
        'created_at' => now()->subMinute(),
    ]);

    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Following]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.feed.index', ['tab' => 'recent']), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('a guest still gets the recent feed', function (): void {
    Question::factory()->create([
        'content' => 'A recent answer.',
        'answer' => 'Yes.',
        'created_at' => now()->subMinute(),
    ]);

    $this->getJson(route('api.v1.feed.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('a profile read reports both follow directions', function (): void {
    $viewer = User::factory()->create();
    $other = User::factory()->create();

    $other->followers()->attach($viewer->id);

    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.show', $other), $headers)
        ->assertOk()
        ->assertJsonPath('data.followed_by_me', true)
        ->assertJsonPath('data.follows_me', false);
});

test('a guest profile read does not invent follow state', function (): void {
    $other = User::factory()->create();

    $this->getJson(route('api.v1.users.show', $other))
        ->assertOk()
        ->assertJsonPath('data.followed_by_me', false)
        ->assertJsonPath('data.follows_me', false);
});
