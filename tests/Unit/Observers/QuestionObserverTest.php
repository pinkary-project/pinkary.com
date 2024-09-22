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
        'content' => '__UPDATE__',
    ]);

    Notification::assertNotSentTo($question->to, QuestionCreated::class);
});

test('send mentioned notification if shared update', function () {
    Notification::fake();

    $user = User::factory()->create();
    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $question = Question::factory()->create([
        'content' => '__UPDATE__',
        'to_id' => $user->id,
        'from_id' => $user->id,
        'answer' => 'this update is for @johndoe!',
        'answer_created_at' => now(),
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
        'content' => '__UPDATE__',
        'to_id' => $user->id,
        'from_id' => $user->id,
        'answer' => 'this is update!',
        'answer_created_at' => now(),
    ]);

    $comment = $question->children()->create([
        'content' => '__UPDATE__',
        'answer' => 'this is a comment',
        'from_id' => $commenter->id,
        'to_id' => $user->id,
        'answer_created_at' => now(),
    ]);

    Notification::assertSentTo($question->from, QuestionCreated::class, function ($notification) use ($comment) {
        return expect($notification->toDatabase($comment->to))->toBe(['question_id' => $comment->id]);
    });
});

test('do not send notification if comment to himself', function () {
    Notification::fake();

    $user = User::factory()->create();
    $question = Question::factory()->create([
        'content' => '__UPDATE__',
        'to_id' => $user->id,
        'from_id' => $user->id,
        'answer' => 'this is update!',
        'answer_created_at' => now(),
    ]);

    $question->children()->create([
        'content' => '__UPDATE__',
        'answer' => 'this is a comment',
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer_created_at' => now(),
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
    $question->update(['answer' => 'answer']);

    expect($question->fresh()->to->notifications()->count())->toBe(0)
        ->and($question->from->notifications()->count())->toBe(1);

    $user = User::factory()->create();
    $question = Question::factory()->create([
        'from_id' => $user->id,
        'to_id' => $user->id,
    ]);

    $question->update(['answer' => 'answer']);
    expect($question->from->notifications()->count())->toBe(0);

    // Question answered and user mentioned in the question content
    $user = User::factory()->create([
        'username' => 'johndoe',
    ]);
    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
    ]);

    $question->update(['answer' => 'answer']);
    expect($user->notifications()->count())->toBe(1);

    // Question answered and user mentioned in the question answer
    $user = User::factory()->create([
        'username' => 'doejohn',
    ]);
    $question = Question::factory()->create(['answer' => null]);

    $question->update(['answer' => 'Im fine @doejohn!']);
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
    $question->update([
        'answer' => 'My favourite developer is to @johndoe',
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

    $question->update(['answer' => 'answer']);
    expect($question->from->notifications()->count())->toBe(1);

    $user = $question->to;
    $question->fresh()->update(['is_ignored' => true]);
    $question = $question->fresh();
    expect($question->to->notifications()->count())->toBe(0);
    expect($question->from->notifications()->count())->toBe(0);

    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);
    $question = Question::factory()
        ->has(Question::factory()->sharedUpdate()->count(3)->state([
            'answer' => 'descendant',
        ]), 'descendants')
        ->create();
    $question->update([
        'answer' => 'My favourite developer is to @johndoe',
    ]);

    Question::factory(3)
        ->sharedUpdate()
        ->for($question, 'parent')
        ->create();

    expect($question->children()->count())->toBe(3);
    expect($question->descendants()->count())->toBe(3);
    expect($question->to->notifications()->count())->toBe(0);
    expect($question->from->notifications()->count())->toBe(1);
    expect($mentionedUser->notifications()->count())->toBe(1);

    $question->fresh()->update(['is_ignored' => true]);
    expect($question->to->fresh()->notifications()->count())->toBe(0);
    expect($question->from->fresh()->notifications()->count())->toBe(0);
    expect($mentionedUser->fresh()->notifications()->count())->toBe(0);
    expect($question->children()->count())->toBe(0);
    expect($question->descendants()->count())->toBe(0);
});

test('deleted', function () {
    $question = Question::factory()->create();
    expect($question->to->notifications()->count())->toBe(1);

    $user = $question->to;
    $question->delete();
    expect($user->fresh()->notifications()->count())->toBe(0);

    $question = Question::factory()->create();
    $question->update([
        'answer' => 'answer',
    ]);

    expect($question->from->notifications()->count())->toBe(1);

    $user = $question->from;
    $question->delete();
    expect($user->fresh()->notifications()->count())->toBe(0);

    $mentionedUser = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $question = Question::factory()
        ->has(Question::factory()->sharedUpdate()->count(3)->state([
            'answer' => 'descendant',
        ]), 'descendants')
        ->create();
    $question->update([
        'answer' => 'My favourite developer is to @johndoe',
    ]);

    Question::factory(3)
        ->sharedUpdate()
        ->has(Question::factory()->sharedUpdate()->count(3)->state([
            'answer' => 'grandchild',
        ]), 'children')
        ->for($question, 'parent')
        ->create();

    expect($question->children()->count())->toBe(3);
    expect(Question::where('answer', 'grandchild')->count())->toBe(9);
    expect(Question::where('answer', 'descendant')->count())->toBe(3);
    expect($question->to->notifications()->count())->toBe(0);
    expect($question->from->notifications()->count())->toBe(1);
    expect($mentionedUser->notifications()->count())->toBe(1);

    $question->delete();
    expect($question->to->fresh()->notifications()->count())->toBe(0);
    expect($question->from->fresh()->notifications()->count())->toBe(0);
    expect($mentionedUser->fresh()->notifications()->count())->toBe(0);
    expect($question->children()->count())->toBe(0);
    expect(Question::where('answer', 'grandchild')->count())->toBe(0);
    expect(Question::where('answer', 'descendant')->count())->toBe(0);
});

test('hashtags are synced when created', function () {
    $question = Question::factory()->create([
        'answer' => 'This answer has a #hashtag.',
    ]);

    expect($question->hashtags->pluck('name')->all())->toBe([
        'hashtag',
    ]);
});

test('hashtags are synced when updated and the content is dirty', function () {
    $question = Question::factory()->create();

    expect($question->hashtags)->toBeEmpty();

    $question->update([
        'content' => 'The content now has a #hashtag.',
    ]);

    expect($question->refresh()->hashtags->pluck('name')->all())->toBe([
        'hashtag',
    ]);
});

test('hashtags are synced when updated and the answer is dirty', function () {
    $question = Question::factory()->create();

    expect($question->hashtags)->toBeEmpty();

    $question->update([
        'answer' => 'The answer now has a #hashtag.',
    ]);

    expect($question->refresh()->hashtags->pluck('name')->all())->toBe([
        'hashtag',
    ]);
});

test('missing hashtags are detached when updated', function () {
    $question = Question::factory()->create([
        'answer' => '#hashtag1 #hashtag2',
    ]);

    expect($question->refresh()->hashtags->pluck('name')->all())->toBe([
        'hashtag1',
        'hashtag2',
    ]);

    $question->update([
        'answer' => '#hashtag1',
    ]);

    expect($question->refresh()->hashtags->pluck('name')->all())->toBe([
        'hashtag1',
    ]);

    $question->update([
        'answer' => 'No hashtags anymore...',
    ]);

    expect($question->refresh()->hashtags)->toBeEmpty();
});

test('hashtags are detached when reported', function () {
    $question = Question::factory()->create([
        'answer' => '#hashtag1',
    ]);

    $question->update([
        'is_reported' => true,
    ]);

    expect($question->hashtags)->toBeEmpty();
});

test('hashtags are detached when ignored', function () {
    $question = Question::factory()->create([
        'answer' => '#hashtag1',
    ]);

    $question->update([
        'is_ignored' => true,
    ]);

    expect($question->hashtags)->toBeEmpty();
});
