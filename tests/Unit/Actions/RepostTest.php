<?php

declare(strict_types=1);

use App\Actions\Questions\CreateRepost;
use App\Actions\Questions\DeleteRepost;
use App\Models\Question;
use App\Models\Repost;
use App\Models\User;
use App\Notifications\QuestionReposted;

test('creating a repost for the same user and question is idempotent', function (): void {
    $question = Question::factory()->create();
    $user = User::factory()->create();

    $first = new CreateRepost()->handle($question, $user);
    $second = new CreateRepost()->handle($question, $user);

    expect($first->is($second))->toBeTrue();
    $this->assertDatabaseCount('reposts', 1);
});

test('deleting a repost removes it', function (): void {
    $repost = Repost::factory()->create();

    $deleted = new DeleteRepost()->handle($repost);

    expect($deleted)->toBeTrue();
    $this->assertDatabaseMissing('reposts', ['id' => $repost->id]);
});

test('undoing a repost keeps notifications for other reposts of the same post', function (): void {
    $owner = User::factory()->create();
    $question = Question::factory()->sharedUpdate()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
    ]);
    $repost = Repost::factory()->create(['question_id' => $question->id]);
    $otherRepost = Repost::factory()->create(['question_id' => $question->id]);

    new DeleteRepost()->handle($repost);

    expect($owner->notifications()->where('type', QuestionReposted::class)->get()->pluck('data.repost_id')->all())
        ->toBe([$otherRepost->id]);
    $this->assertModelExists($otherRepost);
});

test('deleting a post removes its loaded reposts and their notifications', function (): void {
    $owner = User::factory()->create();
    $question = Question::factory()->sharedUpdate()->create([
        'from_id' => $owner->id,
        'to_id' => $owner->id,
    ]);
    $repost = Repost::factory()->create(['question_id' => $question->id]);
    $question->load('reposts');

    $question->delete();

    $this->assertModelMissing($repost);
    expect($owner->notifications()->where('type', QuestionReposted::class)->count())->toBe(0);
});
