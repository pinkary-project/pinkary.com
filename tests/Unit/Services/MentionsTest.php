<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Services\Mentions;

test('mentions from question', function () {
    User::factory()->create(['username' => 'johndoe']);
    User::factory()->create(['username' => 'doejohn']);

    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
        'answer' => 'I am doing great, @doejohn!',
    ]);

    $mentions = (new Mentions())->usersMentioned($question);

    expect($mentions->count())->toBe(2);
    expect($mentions->first()->username)->toBe('johndoe');
    expect($mentions->last()->username)->toBe('doejohn');
});

test('mentions are unique', function () {
    User::factory()->create(['username' => 'johndoe']);

    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
        'answer' => 'I am doing great, @johndoe!',
    ]);

    $mentions = (new Mentions())->usersMentioned($question);

    expect($mentions->count())->toBe(1);
    expect($mentions->first()->username)->toBe('johndoe');
});

test('only valid user mentions are retrieved', function () {
    User::factory()->create(['username' => 'johndoe']);

    $question = Question::factory()->create([
        'content' => 'Hello @johndoe! How are you doing?',
        'answer' => 'I am doing great, @invalid!',
    ]);

    $mentions = (new Mentions())->usersMentioned($question);

    expect($mentions->count())->toBe(1);
    expect($mentions->first()->username)->toBe('johndoe');
});
