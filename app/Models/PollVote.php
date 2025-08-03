<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PollVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $poll_option_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read PollOption $pollOption
 */
final class PollVote extends Model
{
    /** @use HasFactory<PollVoteFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'user_id' => 'integer',
            'poll_option_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the poll vote.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the poll option that owns the poll vote.
     *
     * @return BelongsTo<PollOption, $this>
     */
    public function pollOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class);
    }
}
