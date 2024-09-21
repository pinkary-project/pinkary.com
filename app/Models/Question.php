<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Models\Viewable;
use App\Observers\QuestionObserver;
use App\Services\ParsableContent;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $answer_created_at
 * @property Carbon|null $answer_updated_at
 * @property bool $is_reported
 * @property bool $is_ignored
 * @property int $views
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $from
 * @property-read User $to
 * @property-read Collection<int, Like> $likes
 * @property-read Collection<int, User> $mentions
 * @property-read Question|null $parent
 * @property-read Collection<int, Question> $children
 * @property-read Collection<int, Question> $descendants
 * @property-read Collection<int, Hashtag> $hashtags
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

        return $value !== null && $value !== '' && $value !== '0' ? $content->parse($value) : null;
    }

    /**
     * The attributes that should be cast.
     */
    public function getAnswerAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return $value !== null && $value !== '' && $value !== '0' ? $content->parse($value) : null;
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
            'pinned' => 'bool',
            'is_ignored' => 'boolean',
            'views' => 'integer',
        ];
    }

    /**
     * Get the user that the question was sent from.
     *
     * @return BelongsTo<User, Question>
     */
    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Get the user that the question was sent to.
     *
     * @return BelongsTo<User, Question>
     */
    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarks for the question.
     *
     * @return HasMany<Bookmark>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the likes for the question.
     *
     * @return HasMany<Like>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
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

        preg_match_all("/@([^\s,.?!\/@<]+)/i", type($this->content)->asString(), $contentMatches);
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
     * @return BelongsTo<Question, Question>
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return BelongsTo<Question, Question>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return HasMany<Question>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return HasMany<Question>
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(self::class, 'root_id')
            ->where('is_ignored', false)
            ->where('is_reported', false);
    }

    /**
     * @return BelongsToMany<Hashtag>
     */
    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class);
    }
}
