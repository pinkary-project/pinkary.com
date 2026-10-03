<?php

declare(strict_types=1);

use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function unverifiedUser(): User
{
    return User::factory()->create(['email_verified_at' => null]);
}

test('an unverified user cannot post a question', function (): void {
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Can anyone hear me?',
    ], $headers)->assertForbidden();
});

test('an unverified user cannot answer a question', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->create(['from_id' => $author->id, 'to_id' => $author->id]);
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->putJson(route('api.v1.questions.answer.update', $question), [
        'content' => 'An answer.',
    ], $headers)->assertForbidden();
});

test('an unverified user cannot comment', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->create(['from_id' => $author->id, 'to_id' => $author->id]);
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'A comment.',
    ], $headers)->assertForbidden();
});

test('an unverified user cannot like, bookmark, pin or ignore', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
        'answer' => 'An answer.',
    ]);

    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.like', $question), [], $headers)->assertForbidden();
    $this->deleteJson(route('api.v1.questions.unlike', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.bookmark', $question), [], $headers)->assertForbidden();
    $this->deleteJson(route('api.v1.questions.unbookmark', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.pin', $question), [], $headers)->assertForbidden();
    $this->deleteJson(route('api.v1.questions.unpin', $question), [], $headers)->assertForbidden();
    $this->postJson(route('api.v1.questions.ignore', $question), ['ignored' => true], $headers)->assertForbidden();
});

test('an unverified user cannot vote in a poll', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->poll()->create([
        'from_id' => $author->id,
        'to_id' => $author->id,
    ]);
    $option = PollOption::factory()->for($question)->create(['votes_count' => 0]);

    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.poll.vote', $question), [
        'option_id' => $option->id,
    ], $headers)->assertForbidden();
});

test('an unverified user cannot upload images', function (): void {
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('a.jpg')],
    ], array_merge($headers, ['Accept' => 'application/json']))->assertForbidden();
});

test('an unverified user cannot read bookmarks or notifications', function (): void {
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.bookmarks.index'), $headers)->assertForbidden();
    $this->getJson(route('api.v1.notifications.index'), $headers)->assertForbidden();
    $this->postJson(route('api.v1.notifications.read'), [], $headers)->assertForbidden();
});

test('the refusal is json the client can act on', function (): void {
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Can anyone hear me?',
    ], $headers)->assertForbidden()
        ->assertJsonStructure(['message']);
});

test('an unverified user can still sign in and read their profile', function (): void {
    $user = unverifiedUser();

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('data.verification.email', false)
        ->assertJsonStructure(['token']);

    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.profile.show'), $headers)
        ->assertOk()
        ->assertJsonPath('data.verification.email', false);
});

test('an unverified user can still read the feed and public profiles', function (): void {
    $user = unverifiedUser();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.feed.index'), $headers)->assertOk();
    $this->getJson(route('api.v1.users.show', $user), $headers)->assertOk();
});

test('a verified user is unaffected', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Can anyone hear me?',
    ], $headers)->assertCreated();
});

test('a user whose email changed is re-gated', function (): void {
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->patchJson(route('api.v1.profile.update'), [
        'email' => 'new@example.com',
    ], $headers)->assertOk();

    expect($user->fresh()->email_verified_at)->toBeNull();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Can anyone hear me?',
    ], $headers)->assertForbidden();
});
