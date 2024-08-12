<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\QuestionObserver;
use App\Services\ParsableContent;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $content
 * @property int $question_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Question $question
 */

#[ObservedBy(QuestionObserver::class)]
final class Answer extends Model
{
    use HasFactory;

    /**
     * The casted attributes.
     */
    protected $casts = [
        'content' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be cast.
     */
    public function getContentAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return $value !== null && $value !== '' && $value !== '0' ? $content->parse($value) : null;
    }

    /**
     * The answer's question.
     *
     * @return BelongsTo<Question, Answer>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The user that owns the answer. To_id on the question.
     *
     * @return HasOneThrough<User>
     */
    public function owner(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Question::class, 'id', 'id', 'question_id', 'to_id');
    }
}
