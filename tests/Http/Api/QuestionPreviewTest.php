<?php

declare(strict_types=1);

use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\ParsableContent;
use Illuminate\Http\Request;

/**
 * Pin the parsed payload to stored HTML.
 *
 * A payload fresh for the current raw fields makes the accessors return this
 * HTML verbatim, so the resource's own parsing is exercised instead of the
 * composer, and no link-preview HTTP call is made.
 */
function pinAnswerHtml(Question $question, ?string $html): Question
{
    $raw = $question->getAttributes();

    $content = $raw['content'] ?? null;
    $answer = $raw['answer'] ?? null;

    $question->setRawAttributes(array_merge($raw, [
        'parsed' => json_encode([
            'f' => app(ParsableContent::class)->fingerprint(),
            'c' => is_string($content) ? hash('sha256', $content) : null,
            'a' => is_string($answer) ? hash('sha256', $answer) : null,
            'content' => is_string($content) ? '<p>'.$content.'</p>' : null,
            'answer' => $html,
        ], JSON_THROW_ON_ERROR),
    ]), true);

    return $question;
}

function makeQuestion(): Question
{
    return Question::factory()->create(['anonymously' => false]);
}

function render(Question $question): array
{
    $resource = new QuestionResource($question);

    return $resource->toArray(
        Request::create('https://pinkary.test/api/v1/questions/'.$question->id),
    );
}

beforeEach(function (): void {
    config(['app.url' => 'https://pinkary.test']);
    config(['trusted-hosts.hosts' => []]);

    Request::setTrustedHosts([]);
});

test('a link preview card is exposed with its url, host, title and image', function (): void {
    $question = pinAnswerHtml(makeQuestion(), <<<'HTML'
        <p>Look at this</p>
        <div id="link-preview-card" data-url="https://example.com/articles/one">
            <a href="https://example.com/articles/one">
                <img src="https://cdn.example.com/cover.png" alt="Cover" />
                <h3>An article title</h3>
            </a>
        </div>
        HTML);

    expect(render($question)['preview'])->toBe([
        'url' => 'https://example.com/articles/one',
        'host' => 'example.com',
        'title' => 'An article title',
        'image' => 'https://cdn.example.com/cover.png',
    ]);
});

test('a card carrying a scraped html snippet falls back to the url for its title and image', function (): void {
    // Only the metadata branch of the card emits an <img>/<h3>.
    $question = pinAnswerHtml(makeQuestion(), <<<'HTML'
        <div id="link-preview-card" data-url="https://example.com/articles/two">
            <div class="snippet">Just some scraped markup</div>
        </div>
        HTML);

    expect(render($question)['preview'])->toBe([
        'url' => 'https://example.com/articles/two',
        'host' => 'example.com',
        'title' => 'https://example.com/articles/two',
        'image' => null,
    ]);
});

test('a card with no url is not offered as a preview', function (): void {
    $question = pinAnswerHtml(makeQuestion(), '<div id="link-preview-card" data-url=""></div>');

    expect(render($question)['preview'])->toBeNull();
});

test('a post with no preview card has no preview', function (): void {
    $question = pinAnswerHtml(makeQuestion(), '<p>Nothing to see here</p>');

    expect(render($question)['preview'])->toBeNull();
});

test('post images are listed absolutely and the card image is left out of them', function (): void {
    $question = pinAnswerHtml(makeQuestion(), <<<'HTML'
        <p>First</p>
        <img src="https://cdn.example.com/one.png" alt="" />
        <div id="link-preview-card" data-url="https://example.com/a">
            <img src="https://cdn.example.com/card.png" alt="" />
        </div>
        <p>Second</p>
        <img src="//cdn.example.com/two.png" alt="" />
        HTML);

    // The card image is reported under `preview`, so it is not repeated here.
    expect(render($question)['images'])->toBe([
        'https://cdn.example.com/one.png',
        'https://cdn.example.com/two.png',
    ]);
});

test('a repeated image is listed once and a blank source is dropped', function (): void {
    $question = pinAnswerHtml(makeQuestion(), <<<'HTML'
        <img src="https://cdn.example.com/one.png" alt="" />
        <img src="https://cdn.example.com/one.png" alt="" />
        <img src="" alt="" />
        HTML);

    expect(render($question)['images'])->toBe(['https://cdn.example.com/one.png']);
});

test('an unanswered post with no content has no preview and no images', function (): void {
    // The column is NOT NULL; the accessors resolve '' to null all the same.
    $question = Question::factory()->create(['content' => '', 'answer' => null]);

    $rendered = render($question);

    expect($rendered['preview'])->toBeNull()
        ->and($rendered['images'])->toBe([]);
});

test('a poll whose relations were not loaded reports no options rather than failing', function (): void {
    $question = pinAnswerHtml(makeQuestion(), '<p>Pick one</p>');
    $question->forceFill(['poll_expires_at' => now()->addDay()])->save();

    expect($question->relationLoaded('pollOptions'))->toBeFalse();

    $poll = render($question)['poll'];

    expect($poll['options'])->toBe([])
        ->and($poll['total_votes'])->toBe(0)
        ->and($poll['user_vote_option_id'])->toBeNull()
        ->and($poll['expired'])->toBeFalse();
});
