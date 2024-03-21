<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\QuestionObserver;
use App\Services\ParsableContent;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $from_id
 * @property int $to_id
 * @property bool $pinned
 * @property string $content
 * @property bool $anonymously
 * @property string|null $answer
 * @property Carbon|null $answered_at
 * @property bool $is_reported
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $from
 * @property-read User $to
 * @property-read Collection<int, Like> $likes
 */
#[ObservedBy(QuestionObserver::class)]
final class Question extends Model
{
    use HasFactory, HasUuids;

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
            'answered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'pinned' => 'bool',
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
     * Get the likes for the question.
     *
     * @return HasMany<Like>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
}
