<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Notification;

test('created', function () {
    Notification::fake();

    $question = Question::factory()->create();

    Notification::assertSentTo($question->to, QuestionCreated::class, function ($notification) use ($question) {
        return expect($notification->toDatabase($question->to))->toBe(['question_id' => $question->id]);
    });
});

test('do not send notification if asked himself', function () {
    Notification::fake();

    $user = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $user->id,
        'from_id' => $user->id,
        'is_update' => true,
    ]);

    Notification::assertNotSentTo($question->to, QuestionCreated::class);
});

test('send mentioned notification if shared update', function () {
    Notification::fake();

    $user = User::factory()->create();
    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $question = Question::factory()
        ->create([
            'to_id' => $user->id,
            'from_id' => $user->id,
            'is_update' => true,
            'content' => 'this update is for @johndoe!',
        ]);

    expect($question->mentions()->count())->toBe(1);

    Notification::assertSentTo($mentionedUser, UserMentioned::class, function ($notification) use ($question) {
        return expect($notification->toDatabase($question->to))->toBe(['question_id' => $question->id]);
    });
});

test('send comment notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $commenter = User::factory()->create();

    $question = Question::factory()->create([
        'is_update' => true,
        'to_id' => $user->id,
        'from_id' => $user->id,
        'content' => 'this is update!',
    ]);

    $comment = $question->children()->create([
        'is_update' => true,
        'content' => 'this is a comment',
        'from_id' => $commenter->id,
        'to_id' => $user->id,
    ]);

    Notification::assertSentTo($question->from, QuestionCreated::class, function ($notification) use ($comment) {
        return expect($notification->toDatabase($comment->to))->toBe(['question_id' => $comment->id]);
    });
});

test('do not send notification if comment to himself', function () {
    Notification::fake();

    $user = User::factory()->create();
    $question = Question::factory()->create([
        'is_update' => true,
        'to_id' => $user->id,
        'from_id' => $user->id,
        'content' => 'this is update!',
    ]);

    $question->children()->create([
        'is_update' => true,
        'content' => 'this is a comment',
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    Notification::assertNotSentTo($question->from, QuestionCreated::class);
});

test('updated', function () {
    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);
    $question->update(['is_reported' => true]);

    expect($question->fresh()->to->notifications()->count())->toBe(0);

    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);
    $question->answer()->create([
        'content' => 'answer',
    ]);

    expect($question->fresh()->to->notifications()->count())->toBe(0)
        ->and($question->from->notifications()->count())->toBe(1);

    $user = User::factory()->create();
    $question = Question::factory()
        ->create([
            'from_id' => $user->id,
            'to_id' => $user->id,
            'is_update' => true,
        ]);
    expect($question->from->notifications()->count())->toBe(0);

    // Question answered and user mentioned in the question content
    $user = User::factory()->create([
        'username' => 'johndoe',
    ]);
    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
    ]);

    $question->answer()->create([
        'content' => 'Im fine!',
    ]);
    expect($user->notifications()->count())->toBe(1);

    // Question answered and user mentioned in the question answer
    $user = User::factory()->create([
        'username' => 'doejohn',
    ]);
    $question = Question::factory()->create();
    $question->answer()->create(['content' => 'Im fine @doejohn!']);

    expect($user->notifications()->count())->toBe(1);
});

test('reported', function () {
    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);

    $question->update(['is_reported' => true]);

    expect($question->to->fresh()->notifications()->count())->toBe(0);
    expect($question->from->fresh()->notifications()->count())->toBe(0);
    expect($question->mentions()->count())->toBe(0);

    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);
    $question = Question::factory()->create();
    $question->answer()->create([
        'content' => 'My favourite developer is to @johndoe',
    ]);

    Question::factory(3)
        ->sharedUpdate()
        ->for($question, 'parent')
        ->create();

    expect($question->children()->count())->toBe(3);

    $question->update(['is_reported' => true]);

    expect($question->to->fresh()->notifications()->count())->toBe(0);
    expect($question->from->fresh()->notifications()->count())->toBe(0);
    expect($mentionedUser->fresh()->notifications()->count())->toBe(0);
    expect($question->children()->count())->toBe(0);
});

test('ignored', function () {
    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);

    $question->answer()->create([
        'content' => 'answer',
    ]);
    expect($question->from->notifications()->count())->toBe(1);

    $user = $question->to;
    $question->fresh()->update(['is_ignored' => true]);
    $question = $question->fresh();
    expect($question->to->notifications()->count())->toBe(0);
    expect($question->from->notifications()->count())->toBe(0);

    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);
    $question = Question::factory()->create();
    $question->answer()->create([
        'content' => 'My favourite developer is to @johndoe',
    ]);

    Question::factory(3)
        ->sharedUpdate()
        ->for($question, 'parent')
        ->create();

    expect($question->children()->count())->toBe(3)
        ->and($question->to->notifications()->count())->toBe(0)
        ->and($question->from->notifications()->count())->toBe(1)
        ->and($mentionedUser->notifications()->count())->toBe(1);

    $question->fresh()->update(['is_ignored' => true]);
    expect($question->to->fresh()->notifications()->count())->toBe(0)
        ->and($question->from->fresh()->notifications()->count())->toBe(0)
        ->and($mentionedUser->fresh()->notifications()->count())->toBe(0)
        ->and($question->children()->count())->toBe(0);
});

test('deleted', function () {
    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);

    $user = $question->to;
    $question->delete();
    expect($user->fresh()->notifications()->count())->toBe(0);

    $question = Question::factory()->create();
    $question->answer()->create([
        'content' => 'answer',
    ]);

    expect($question->from->notifications()->count())->toBe(1);

    $user = $question->from;
    $question->delete();
    expect($user->fresh()->notifications()->count())->toBe(0);

    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $question = Question::factory()->create();
    $question->answer()->create([
        'content' => 'My favourite developer is to @johndoe',
    ]);

    Question::factory(3)
        ->sharedUpdate()
        ->has(Question::factory()->sharedUpdate()->count(3)->state([
            'content' => 'grandchild',
        ]), 'children')
        ->for($question, 'parent')
        ->create();

    expect($question->children()->count())->toBe(3)
        ->and(Question::where('content', 'grandchild')->count())->toBe(9)
        ->and($question->to->notifications()->count())->toBe(0)
        ->and($question->from->notifications()->count())->toBe(1)
        ->and($mentionedUser->notifications()->count())->toBe(1)
        ->and($question->refresh()->mentions()->first()->username)->toContain('johndoe');

    $question->delete();
    expect($question->to->fresh()->notifications()->count())->toBe(0)
        ->and($question->from->fresh()->notifications()->count())->toBe(0)
        ->and($mentionedUser->fresh()->notifications()->count())->toBe(0)
        ->and($question->children()->count())->toBe(0)
        ->and(Question::where('content', 'grandchild')->count())->toBe(0);
});
