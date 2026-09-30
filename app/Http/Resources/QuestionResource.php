<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;
use App\Support\AbsoluteUrl;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @property Question $resource */
final class QuestionResource extends JsonResource
{
    /**
     * Memoized result of parsedAnswer(); null when the post has no HTML.
     */
    private ?DOMDocument $parsedAnswer = null;

    /**
     * Whether parsedAnswer() has already run.
     *
     * A separate flag because a post with no content legitimately resolves to
     * null, and resolving it reads two accessors that can each re-parse.
     */
    private bool $answerParsed = false;

    /**
     * The post in the shape the clients render.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'pinned' => (bool) $this->resource->pinned,
            // '__UPDATE__' is the shared-update sentinel, not content.
            'is_update' => $this->resource->isSharedUpdate(),
            'content' => $this->resource->isSharedUpdate() ? null : $this->resource->sharable_content,
            'answer' => $this->resource->sharable_answer,
            'anonymously' => (bool) $this->resource->anonymously,
            'views' => $this->resource->views,
            'created_at' => $this->resource->created_at->toIso8601String(),
            'updated_at' => ($this->resource->answer_updated_at ?: $this->resource->answer_created_at)?->toIso8601String(),
            'edited' => $this->resource->answer_updated_at !== null,
            'answered_at' => $this->resource->answer_created_at?->toIso8601String(),
            'from' => ! $this->resource->anonymously
                ? $this->author($this->resource->from, $request)
                : null,
            'to' => $this->author($this->resource->to, $request),
            'thread' => [
                'parent_id' => $this->resource->parent_id,
                'root_id' => $this->resource->root_id,
                'posts' => self::collection(
                    $this->resource->relationLoaded('threadChain') ? $this->resource->threadChain : []
                ),
                // Read raw attributes: partially-selected models throw
                // MissingAttributeException on dynamic getAttribute().
                'more' => (bool) ($this->resource->getAttributes()['threadMore'] ?? false),
                'more_id' => $this->resource->getAttributes()['threadMoreId'] ?? null,
            ],
            'channel' => $this->channel(),
            'poll' => $this->poll(),
            'preview' => $this->preview($request),
            'images' => $this->images($request),
            'metrics' => [
                'likes' => (int) ($this->resource->likes_count ?? 0),
                'comments' => (int) ($this->resource->children_count ?? 0),
                'bookmarks' => (int) ($this->resource->bookmarks_count ?? 0),
                'liked' => (bool) ($this->resource->is_liked ?? false),
                'bookmarked' => (bool) ($this->resource->is_bookmarked ?? false),
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    /**
     * The post's poll, or null when it has none.
     *
     * @return array<string, mixed>|null
     */
    public function poll(): ?array
    {
        if ($this->resource->poll_expires_at === null) {
            return null;
        }

        /** @var Collection<int, PollOption> $options */
        $options = $this->resource->relationLoaded('pollOptions')
            ? $this->resource->pollOptions
            : collect();

        $vote = $this->resource->relationLoaded('pollVotes')
            ? $this->resource->pollVotes->first()
            : null;

        return [
            'expires_at' => $this->resource->poll_expires_at->toIso8601String(),
            'expired' => $this->resource->isPollExpired(),
            'time_remaining' => $this->resource->getPollTimeRemaining(),
            'total_votes' => $options->sum(fn (PollOption $option): int => $option->votes_count),
            'user_vote_option_id' => $vote?->poll_option_id,
            'options' => $options->map(fn (PollOption $option): array => [
                'id' => $option->id,
                'text' => $option->text,
                'votes' => (int) $option->votes_count,
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function author(User $user, Request $request): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => AbsoluteUrl::for($user->avatar_url, $request),
            'verified' => (bool) $user->is_verified,
            'company_verified' => (bool) $user->is_company_verified,
        ];
    }

    /** @return array<string, mixed>|null */
    private function channel(): ?array
    {
        $channel = $this->resource->relationLoaded('channel') ? $this->resource->channel : null;

        if (! $channel) {
            return null;
        }

        return [
            'id' => $channel->id,
            'name' => $channel->name,
            'slug' => $channel->slug,
        ];
    }

    /** @return array<string, mixed>|null */
    private function preview(Request $request): ?array
    {
        $card = $this->firstPreviewCard();

        if (! $card instanceof DOMElement) {
            return null;
        }

        $url = $card->getAttribute('data-url');

        if ($url === '') {
            return null;
        }

        $titleNode = $card->getElementsByTagName('h3')->item(0);
        $imageNode = $card->getElementsByTagName('img')->item(0);

        return [
            'url' => $url,
            'host' => (string) (parse_url($url, PHP_URL_HOST) ?: $url),
            'title' => $titleNode ? mb_trim($titleNode->textContent) : $url,
            'image' => $imageNode
                ? AbsoluteUrl::fromPage($imageNode->getAttribute('src'), $url, $request)
                : null,
        ];
    }

    /** @return list<string> */
    private function images(Request $request): array
    {
        $document = $this->parsedAnswer();

        if (! $document instanceof DOMDocument) {
            return [];
        }

        $images = [];

        foreach ($document->getElementsByTagName('img') as $img) {
            $ancestor = $img->parentNode;
            $inPreview = false;

            while ($ancestor) {
                if ($ancestor->nodeName === 'div' && $ancestor instanceof DOMElement && $ancestor->getAttribute('id') === 'link-preview-card') {
                    $inPreview = true;

                    break;
                }

                $ancestor = $ancestor->parentNode;
            }

            if ($inPreview) {
                continue;
            }

            $src = AbsoluteUrl::for($img->getAttribute('src'), $request);

            if ($src !== null && ! in_array($src, $images, true)) {
                $images[] = $src;
            }
        }

        return $images;
    }

    /**
     * The link preview card embedded in the stored HTML, if the post has one.
     */
    private function firstPreviewCard(): ?DOMElement
    {
        $document = $this->parsedAnswer();

        if (! $document instanceof DOMDocument) {
            return null;
        }

        foreach ($document->getElementsByTagName('div') as $div) {
            if ($div->getAttribute('id') === 'link-preview-card') {
                return $div;
            }
        }

        return null;
    }

    /**
     * The post's answer, or its content when unanswered, as a parsed document.
     */
    private function parsedAnswer(): ?DOMDocument
    {
        // toArray() calls preview() and images(), and a nested
        // QuestionResource re-enters this per thread ancestor, so an
        // unmemoized parse repeats ~120 times on a 20-post reply page.
        if ($this->answerParsed) {
            return $this->parsedAnswer;
        }

        $this->answerParsed = true;

        // The stored HTML already embeds the preview card and images, so
        // parse it instead of issuing HTTP requests for link metadata.
        $html = $this->resource->answer ?? $this->resource->content;

        if (! is_string($html) || mb_trim($html) === '') {
            return $this->parsedAnswer = null;
        }

        $document = new DOMDocument;

        set_error_handler(static fn (): bool => true);

        try {
            $document->loadHTML('<?xml encoding="utf-8"?>'.$html);
        } finally {
            restore_error_handler();
        }

        return $this->parsedAnswer = $document;
    }
}
