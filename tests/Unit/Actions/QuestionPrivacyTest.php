<?php

declare(strict_types=1);

use App\Actions\Questions\CreateQuestion;
use App\Actions\Questions\CreateQuestionToUser;
use App\Enums\UserQuestionPreference;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

test('question preferences default to everyone', function (): void {
    $user = User::factory()->create();

    expect($user->question_preference)->toBe(UserQuestionPreference::Everyone)
        ->and(UserQuestionPreference::toArray())->toBe([
            'everyone' => 'Everyone',
            'following' => 'Following',
            'no_one' => 'No one',
        ]);
});

test('question access follows the recipients preference and following direction', function (string $preference, bool $recipientFollows, bool $senderFollows, bool $allowed): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => $preference]);

    if ($recipientFollows) {
        $recipient->following()->attach($sender);
    }

    if ($senderFollows) {
        $sender->following()->attach($recipient);
    }

    expect(Gate::forUser($sender)->allows('askQuestion', $recipient))->toBe($allowed);
})->with([
    'everyone' => ['everyone', false, false, true],
    'recipient follows sender' => ['following', true, false, true],
    'sender follows recipient' => ['following', false, true, false],
    'stranger' => ['following', false, false, false],
    'questions off' => ['no_one', true, true, false],
]);

test('guests can only start the open question experience', function (string $preference, bool $allowed): void {
    $recipient = User::factory()->create(['question_preference' => $preference]);

    expect(Gate::forUser(null)->allows('askQuestion', $recipient))->toBe($allowed);
})->with([
    ['everyone', true],
    ['following', false],
    ['no_one', false],
]);

test('only the owner can change a question preference', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    expect(Gate::forUser($owner)->allows('updateQuestionPreference', $owner))->toBeTrue()
        ->and(Gate::forUser($stranger)->allows('updateQuestionPreference', $owner))->toBeFalse()
        ->and(Gate::forUser(null)->allows('updateQuestionPreference', $owner))->toBeFalse();
});

test('question write actions enforce the latest preference without side effects', function (bool $apiAction): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    User::query()->whereKey($recipient->id)->update(['question_preference' => 'no_one']);

    $write = fn (): mixed => $apiAction
        ? app(CreateQuestionToUser::class)->handle($sender, $recipient, 'Hello!')
        : app(CreateQuestion::class)->handle($sender, [['to_id' => $recipient->id, 'content' => 'Hello!']], [], [], null);

    expect($write)->toThrow(AuthorizationException::class);
    $this->assertDatabaseCount('questions', 0);
    $this->assertDatabaseCount('notifications', 0);
})->with([true, false]);

test('both question write actions allow people the recipient follows', function (bool $apiAction): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => 'following']);
    $recipient->following()->attach($sender);

    if ($apiAction) {
        app(CreateQuestionToUser::class)->handle($sender, $recipient, 'Hello!', true);
    } else {
        app(CreateQuestion::class)->handle($sender, [['to_id' => $recipient->id, 'content' => 'Hello!', 'anonymously' => true]], [], [], null);
    }

    expect(Question::sole()->anonymously)->toBeTrue();
})->with([true, false]);
