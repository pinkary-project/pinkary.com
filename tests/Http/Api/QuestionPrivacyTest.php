<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('API user list question availability does not add queries per user', function (string $endpoint, bool $followsViewer): void {
    $viewer = User::factory()->create();
    $users = User::factory()->count(10)->create(['question_preference' => 'following']);
    $viewer->following()->attach($users->modelKeys());

    if ($followsViewer) {
        $viewer->followers()->attach($users->modelKeys());
    }

    if ($endpoint === 'likes') {
        $question = Question::factory()->create(['to_id' => $viewer->id]);
        $question->likes()->createMany($users->map(fn (User $user): array => ['user_id' => $user->id])->all());
        $url = route('api.v1.questions.likes.index', $question);
    } else {
        $target = User::factory()->create();
        $target->{$endpoint}()->attach($users->modelKeys());
        $url = route('api.v1.users.'.$endpoint.'.index', $target->username);
    }

    $headers = ['Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken];
    $followQueryCounts = [];

    foreach ([1, 10] as $size) {
        auth()->forgetGuards();
        DB::enableQueryLog();
        DB::flushQueryLog();

        try {
            $response = $this->getJson($url.'?per_page='.$size, $headers)->assertOk()->assertJsonCount($size, 'data');
            $followQueryCounts[] = collect(DB::getQueryLog())
                ->filter(fn (array $entry): bool => str_starts_with($entry['query'], 'select exists(') && str_contains($entry['query'], 'followers'))
                ->count();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        foreach ($response->json('data') as $user) {
            expect($user['can_ask_question'])->toBe($followsViewer)
                ->and($user['follows_me'])->toBe($followsViewer)
                ->and($user['followed_by_me'])->toBeTrue();
        }
    }

    expect($followQueryCounts)->toBe([0, 0]);
})->with(['followers', 'following', 'likes'])->with([true, false]);

test('API profiles communicate question availability without exposing another users preference', function (string $preference, bool $followed, bool $allowed): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => $preference]);
    if ($followed) {
        $recipient->following()->attach($sender);
    }

    $headers = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];
    $this->getJson(route('api.v1.users.show', $recipient->username), $headers)
        ->assertOk()->assertJsonPath('data.can_ask_question', $allowed)
        ->assertJsonPath('data.question_preference', null);

    $response = $this->postJson(route('api.v1.questions.store'), [
        'to_username' => $recipient->username,
        'content' => 'Hello!',
    ], $headers);

    if ($allowed) {
        $response->assertCreated();
        expect(Question::sole()->to_id)->toBe($recipient->id);
    } else {
        $response->assertForbidden();
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('notifications', 0);
    }
})->with([
    ['everyone', false, true],
    ['following', true, true],
    ['following', false, false],
    ['no_one', true, false],
]);

test('API profile updates validate and save question preferences', function (string $preference): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => $preference], $headers)
        ->assertOk()->assertJsonPath('data.question_preference', $preference);

    expect($user->refresh()->question_preference->value)->toBe($preference);

    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => 'followers'], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('question_preference');

    expect($user->refresh()->question_preference->value)->toBe($preference);
})->with(['everyone', 'following', 'no_one']);

test('API posts remain available when the author has turned questions off', function (): void {
    $user = User::factory()->create(['question_preference' => 'no_one']);

    $this->postJson(route('api.v1.questions.store'), ['content' => 'My update'], [
        'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken,
    ])->assertCreated();

    expect(Question::sole()->answer)->toBe('My update');
});

test('API question availability requires a verified visitor other than the owner', function (string $visitor): void {
    $recipient = User::factory()->create();
    $sender = match ($visitor) {
        'guest' => null,
        'unverified' => User::factory()->unverified()->create(),
        'owner' => $recipient,
    };
    $headers = $sender === null ? [] : ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.show', $recipient->username), $headers)
        ->assertOk()->assertJsonPath('data.can_ask_question', false);

    if ($visitor !== 'owner') {
        $this->postJson(route('api.v1.questions.store'), [
            'to_username' => $recipient->username,
            'content' => 'Hello!',
        ], $headers)->assertStatus($sender === null ? 401 : 403);

        $this->assertDatabaseCount('questions', 0);
    }
})->with(['guest', 'unverified', 'owner']);

test('API preference and follow changes apply to anonymous and named questions on the next request', function (bool $anonymously): void {
    $recipient = User::factory()->create();
    $sender = User::factory()->create();
    $ownerHeaders = ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken];
    $senderHeaders = ['Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken];
    $payload = ['to_username' => $recipient->username, 'content' => 'Hello!', 'anonymously' => $anonymously];

    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => 'following'], $ownerHeaders)
        ->assertOk()->assertJsonPath('data.question_preference', 'following');

    auth()->forgetGuards();
    $this->getJson(route('api.v1.users.show', $recipient->username), $senderHeaders)
        ->assertOk()->assertJsonPath('data.can_ask_question', false);
    $this->postJson(route('api.v1.questions.store'), $payload, $senderHeaders)->assertForbidden();

    auth()->forgetGuards();
    $this->postJson(route('api.v1.users.follow', $sender->username), [], $ownerHeaders)->assertOk();

    auth()->forgetGuards();
    $this->getJson(route('api.v1.users.show', $recipient->username), $senderHeaders)
        ->assertOk()->assertJsonPath('data.can_ask_question', true);
    $this->postJson(route('api.v1.questions.store'), $payload, $senderHeaders)
        ->assertCreated()->assertJsonPath('data.0.anonymously', $anonymously)
        ->assertJsonPath('data.0.from.id', $anonymously ? null : $sender->id);

    auth()->forgetGuards();
    $this->deleteJson(route('api.v1.users.unfollow', $sender->username), [], $ownerHeaders)->assertOk();

    auth()->forgetGuards();
    $this->getJson(route('api.v1.users.show', $recipient->username), $senderHeaders)
        ->assertOk()->assertJsonPath('data.can_ask_question', false);
    $this->postJson(route('api.v1.questions.store'), $payload, $senderHeaders)->assertForbidden();

    auth()->forgetGuards();
    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => 'no_one'], $ownerHeaders)->assertOk();

    auth()->forgetGuards();
    $this->postJson(route('api.v1.questions.store'), $payload, $senderHeaders)->assertForbidden();

    auth()->forgetGuards();
    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => 'everyone'], $ownerHeaders)->assertOk();

    auth()->forgetGuards();
    $this->getJson(route('api.v1.users.show', $recipient->username), $senderHeaders)
        ->assertOk()->assertJsonPath('data.can_ask_question', true);
    $this->postJson(route('api.v1.questions.store'), $payload, $senderHeaders)->assertCreated();

    expect($recipient->questionsReceived()->count())->toBe(2)
        ->and($recipient->notifications()->count())->toBe(2)
        ->and($sender->refresh()->question_preference->value)->toBe('everyone');
})->with([true, false]);

test('API comments remain available when both users have turned questions off', function (): void {
    $sender = User::factory()->create(['question_preference' => 'no_one']);
    $recipient = User::factory()->create(['question_preference' => 'no_one']);
    $post = Question::factory()->create([
        'from_id' => $recipient->id, 'to_id' => $recipient->id,
        'content' => '__UPDATE__', 'answer' => 'Existing post.',
    ]);

    $this->postJson(route('api.v1.questions.comments.store', $post), ['content' => 'A comment.'], [
        'Authorization' => 'Bearer '.$sender->createToken('test')->plainTextToken,
    ])->assertCreated()->assertJsonPath('data.answer', 'A comment.')
        ->assertJsonPath('data.to.id', $sender->id)
        ->assertJsonPath('data.thread.parent_id', $post->id)
        ->assertJsonPath('data.thread.root_id', $post->id);

    $this->assertDatabaseCount('questions', 2);
});

test('API questions sent before privacy changes can still be answered', function (): void {
    $recipient = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $recipient->id, 'answer' => null, 'answer_created_at' => null]);
    $headers = ['Authorization' => 'Bearer '.$recipient->createToken('test')->plainTextToken];

    $this->patchJson(route('api.v1.profile.update'), ['question_preference' => 'no_one'], $headers)->assertOk();
    $this->putJson(route('api.v1.questions.answer.update', $question), ['answer' => 'My answer.'], $headers)
        ->assertOk()->assertJsonPath('data.answer', 'My answer.');

    expect($question->refresh()->answer)->toBe('My answer.');
});
