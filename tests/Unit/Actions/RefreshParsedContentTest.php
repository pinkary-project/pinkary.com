<?php

declare(strict_types=1);

use App\Models\Question;
use App\Services\ParsableContent;

test('a payload that is valid json but not an object is reparsed instead of trusted', function (): void {
    $question = Question::factory()->create();

    // Valid JSON scalars bypass the JsonException guard.
    $question->setRawAttributes(
        array_merge($question->getAttributes(), ['parsed' => '5']),
        true,
    );

    expect($question->content)->toBeString()
        ->and($question->content)->toContain((string) $question->getAttributes()['content'])
        ->and($question->getAttributes()['parsed'])->toStartWith('{');
});

test('a payload that is not json at all is reparsed instead of trusted', function (): void {
    $question = Question::factory()->create();

    $question->setRawAttributes(
        array_merge($question->getAttributes(), ['parsed' => 'not json at all']),
        true,
    );

    expect($question->content)->toBeString()
        ->and($question->getAttributes()['parsed'])->toStartWith('{');
});

test('an empty payload is reparsed instead of trusted', function (): void {
    $question = Question::factory()->create();

    $question->setRawAttributes(
        array_merge($question->getAttributes(), ['parsed' => '']),
        true,
    );

    expect($question->content)->toBeString()
        ->and($question->getAttributes()['parsed'])->toStartWith('{');
});

test('a payload written by an older provider set is reparsed', function (): void {
    $question = Question::factory()->create();

    $question->setRawAttributes(
        array_merge($question->getAttributes(), ['parsed' => '{"f":"stale","content":"<p>old</p>"}']),
        true,
    );

    expect($question->content)->not->toBe('<p>old</p>');
});

test('a payload whose hash no longer matches the raw field is reparsed', function (): void {
    $question = Question::factory()->create();
    $raw = $question->getAttributes();

    $question->setRawAttributes(array_merge($raw, [
        'parsed' => json_encode([
            'f' => app(ParsableContent::class)->fingerprint(),
            'c' => hash('sha256', 'something else entirely'),
            'content' => '<p>stale</p>',
        ], JSON_THROW_ON_ERROR),
    ]), true);

    expect($question->content)->not->toBe('<p>stale</p>');
});
