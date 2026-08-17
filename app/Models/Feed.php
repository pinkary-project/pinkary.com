<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedVisibility;
use Carbon\CarbonImmutable;
use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property FeedVisibility $visibility
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read User $user
 * @property-read Collection<int, Topic> $topics
 * @property-read Collection<int, User> $people
 * @property-read Collection<int, User> $followers
 * @property-read int|null $followers_count
 * @property-read int|null $topics_count
 * @property-read int|null $people_count
 */
final class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'visibility' => FeedVisibility::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the owner of the feed.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the topics included in this feed.
     *
     * @return BelongsToMany<Topic, $this>
     */
    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'feed_topic');
    }

    /**
     * Get the people included in this feed.
     *
     * @return BelongsToMany<User, $this>
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'feed_user');
    }

    /**
     * Get the users following this feed.
     *
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'feed_followers');
    }

    /**
     * Scope a query to only include public feeds or feeds visible to the given user.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('visibility', FeedVisibility::Public->value);

            if ($user !== null) {
                $query->orWhere('user_id', $user->id);
            }
        });
    }

    /**
     * Determine if this feed is public.
     */
    public function isPublic(): bool
    {
        return $this->visibility === FeedVisibility::Public;
    }

    /**
     * Determine if the given user is following this feed.
     */
    public function isFollowedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->followers()->where('users.id', $user->id)->exists();
    }

    /**
     * Bootstrap the model.
     */
    protected static function booted(): void
    {
        self::creating(function (Feed $feed): void {
            if (blank($feed->slug)) {
                $baseSlug = Str::slug($feed->name);
                $slug = $baseSlug;
                $counter = 1;

                while (self::query()->where('user_id', $feed->user_id)->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $feed->slug = $slug;
            }
        });
    }
}
