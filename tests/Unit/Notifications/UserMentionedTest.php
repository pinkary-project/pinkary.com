<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Notifications\UserMentioned;

test('to database', function () {
    $user = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
    ]);

    $notification = new UserMentioned($question);

    expect($notification->toDatabase($user))->toBe(['question_id' => $question->id]);
});
