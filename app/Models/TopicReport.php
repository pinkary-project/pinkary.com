<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TopicReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $question_id
 * @property int $reporter_id
 * @property int $current_topic_id
 * @property int|null $suggested_topic_id
 * @property string|null $reason
 * @property string $status
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Question $question
 * @property-read User $reporter
 * @property-read Topic $currentTopic
 * @property-read Topic|null $suggestedTopic
 */
final class TopicReport extends Model
{
    /** @use HasFactory<TopicReportFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the question that was reported.
     *
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user who reported the question.
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the current topic of the reported question.
     *
     * @return BelongsTo<Topic, $this>
     */
    public function currentTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'current_topic_id');
    }

    /**
     * Get the suggested topic for the reported question.
     *
     * @return BelongsTo<Topic, $this>
     */
    public function suggestedTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'suggested_topic_id');
    }
}
