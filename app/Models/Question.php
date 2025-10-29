<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Models\Viewable;
use App\Observers\QuestionObserver;
use App\Services\ParsableContent;
use Carbon\CarbonImmutable;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string $id
 * @property string|null $root_id
 * @property string|null $parent_id
 * @property int $from_id
 * @property int $to_id
 * @property bool $pinned
 * @property string $content
 * @property bool $anonymously
 * @property string|null $answer
 * @property CarbonImmutable|null $answer_created_at
 * @property CarbonImmutable|null $answer_updated_at
 * @property CarbonImmutable|null $poll_expires_at
 * @property bool $is_reported
 * @property bool $is_ignored
 * @property int $views
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read User $from
 * @property-read User $to
 * @property-read Collection<int, Like> $likes
 * @property-read Collection<int, User> $mentions
 * @property-read Question|null $parent
 * @property-read Collection<int, Question> $children
 * @property-read Collection<int, Question> $descendants
 * @property-read Collection<int, Hashtag> $hashtags
 * @property-read Collection<int, PollOption> $pollOptions
 */
#[ObservedBy(QuestionObserver::class)]
final class Question extends Model implements Viewable
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory, HasUuids;

    /**
     * Increment the views for the given question IDs.
     */
    public static function incrementViews(array $ids): void
    {
        self::withoutTimestamps(function () use ($ids): void {
            self::query()
                ->whereIn('id', $ids)
                ->whereNotNull('answer')
                ->increment('views');
        });
    }

    /**
     * The attributes that should be cast.
     */
    public function getContentAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return in_array($value, [null, '', '0'], true) ? null : $content->parse($value);
    }

    /**
     * The attributes that should be cast.
     */
    public function getAnswerAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return in_array($value, [null, '', '0'], true) ? null : $content->parse($value);
    }

    /**
     * The attributes that should be cast.
     */
    public function getSharableAnswerAttribute(): ?string
    {
        return $this->getSharableText($this->answer);
    }

    /**
     * The attributes that should be cast.
     */
    public function getSharableContentAttribute(): ?string
    {
        return $this->getSharableText($this->content);
    }

    /**
     * Get the sharable text for the given content.
     */
    public function getSharableText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = preg_replace('/<div\s+id="link-preview-card"[^>]*>(.*)<\/div>(?!.*<\/div>)/si', '', $text);

        $text = preg_replace(
            '/<pre><code.*?>.*?<\/code><\/pre>/si',
            ' [👀 see the code on Pinkary 👀] ',
            (string) $text
        );

        $text = str_replace('<br>', ' ', (string) $text);

        return strip_tags($text);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'is_reported' => 'boolean',
            'anonymously' => 'boolean',
            'answer_created_at' => 'datetime',
            'answer_updated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'pinned' => 'boolean',
            'is_ignored' => 'boolean',
            'poll_expires_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    /**
     * Get the user that the question was sent from.
     *
     * @return BelongsTo<User, $this>
     */
    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Get the user that the question was sent to.
     *
     * @return BelongsTo<User, $this>
     */
    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarks for the question.
     *
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the likes for the question.
     *
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the likers for the question.
     *
     * @return HasManyThrough<User, Like, $this>
     */
    public function likers(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Like::class, 'question_id', 'id', 'id', 'user_id');
    }

    /**
     * Get the mentions for the question.
     *
     * @return Collection<int, User>
     */
    public function mentions(): Collection
    {
        if (is_null($this->answer)) {
            /** @var Collection<int, User> $mentionedUsers */
            $mentionedUsers = new Collection();

            return $mentionedUsers;
        }

        preg_match_all("/@([^\s,.?!\/@<]+)/i", (string) $this->content, $contentMatches);
        preg_match_all("/@([^\s,.?!\/@<]+)/i", $this->answer, $answerMatches);

        $mentions = array_unique(array_merge($contentMatches[1], $answerMatches[1]));

        return User::whereIn('username', $mentions)->get();
    }

    /**
     * Determine if the question is shared update.
     */
    public function isSharedUpdate(): bool
    {
        return $this->from_id === $this->to_id && $this->content === '__UPDATE__';
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(self::class, 'root_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return BelongsToMany<Hashtag, $this>
     */
    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class);
    }

    /**
     * Get the poll options for the question.
     *
     * @return HasMany<PollOption, $this>
     */
    public function pollOptions(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    /**
     * Check if this question is a poll.
     */
    public function isPoll(): bool
    {
        return $this->poll_expires_at !== null;
    }

    /**
     * Check if the poll has expired.
     */
    public function isPollExpired(): bool
    {
        return (bool) $this->poll_expires_at?->isPast();
    }

    /**
     * Get the time remaining for the poll.
     */
    public function getPollTimeRemaining(): ?string
    {
        if ($this->isPollExpired()) {
            return null;
        }

        return $this->poll_expires_at?->diffForHumans();
    }
}
