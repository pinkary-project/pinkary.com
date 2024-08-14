<?php

declare(strict_types=1);

use App\EventActions\UpdateQuestionHashtags;
use App\Models\Hashtag;
use App\Models\Question;

it('attaches the newly parsed hashtags', function () {
    $question = Question::factory()->create();

    $question->answer = '#hashtag1 #hashtag2';

    $synced = (new UpdateQuestionHashtags($question))->handle();

    $hashtag1 = Hashtag::query()->firstWhere('name', 'hashtag1');
    $hashtag2 = Hashtag::query()->firstWhere('name', 'hashtag2');

    expect($synced)->toBe([
        'attached' => [
            $hashtag1->id,
            $hashtag2->id,
        ],
        'detached' => [],
        'updated' => [],
    ])
        ->and($question->hashtags->pluck('name')->all())->toBe(['hashtag1', 'hashtag2']);
});

it('detaches hashtags no longer found in the question', function () {
    $question = Question::factory()->create([
        'answer' => '#hashtag1 #hashtag2',
    ]);

    expect($question->hashtags->pluck('name')->all())->toBe(['hashtag1', 'hashtag2']);

    $question->answer = '#hashtag3';

    $synced = (new UpdateQuestionHashtags($question))->handle();

    $hashtag1 = Hashtag::query()->firstWhere('name', 'hashtag1');
    $hashtag2 = Hashtag::query()->firstWhere('name', 'hashtag2');
    $hashtag3 = Hashtag::query()->firstWhere('name', 'hashtag3');

    expect($synced)->toBe([
        'attached' => [
            $hashtag3->id,
        ],
        'detached' => [
            $hashtag1->id,
            $hashtag2->id,
        ],
        'updated' => [],
    ])
        ->and($question->refresh()->hashtags->pluck('name')->all())->toBe(['hashtag3']);
});

it('will not parse hashtags within code and links', function () {
    $question = Question::factory()->create();

    $question->answer = <<<'ANSWER'
            ```php
            // some code with a #hashtag here.
            $url = https://example.com/route#segment
            ```

            Check out this link with a segment: https://example.com/route#segment

            But the #cool hashtag should be synced!
            ANSWER;

    (new UpdateQuestionHashtags($question))->handle();

    expect($question->hashtags->pluck('name')->all())->toBe(['cool']);
});
