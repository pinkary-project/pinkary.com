<?php

declare(strict_types=1);

use App\Actions\Questions\CreateRepost;
use App\Actions\Questions\DeleteRepost;
use App\Models\Question;
use App\Models\Repost;
use App\Models\User;

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
