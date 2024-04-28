<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ParsableContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $owner_id
 * @property int $question_id
 * @property string|null $content
 * @property bool $is_reported
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ?string $raw_content
 * @property-read User $owner
 * @property-read Question $question
 */
final class Comment extends Model
{
    use HasFactory, HasUuids;

    /**
     * The content attribute accessor.
     */
    public function getContentAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return $value !== null && $value !== '' && $value !== '0' ? $content->parse($value) : null;
    }

    /**
     * The raw content attribute accessor.
     */
    public function getRawContentAttribute(): ?string
    {
        return $this->attributes['content'];
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'content' => 'string',
            'is_reported' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the Comment
     *
     * @return BelongsTo<User, Comment>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the question that owns the Comment
     *
     * @return BelongsTo<Question, Comment>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
