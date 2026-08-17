<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property bool $is_discoverable
 * @property bool $is_system
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Collection<int, Question> $questions
 * @property-read Collection<int, User> $followers
 * @property-read Collection<int, Feed> $feeds
 * @property-read int|null $followers_count
 * @property-read int|null $questions_count
 */
final class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_discoverable' => 'boolean',
            'is_system' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the questions associated with this topic.
     *
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the users following this topic.
     *
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'topic_user');
    }

    /**
     * Get the feeds including this topic.
     *
     * @return BelongsToMany<Feed, $this>
     */
    public function feeds(): BelongsToMany
    {
        return $this->belongsToMany(Feed::class, 'feed_topic');
    }

    /**
     * Determine if the given user is following this topic.
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
        self::creating(function (Topic $topic): void {
            if (blank($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }
}
