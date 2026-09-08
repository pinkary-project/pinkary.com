<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final readonly class UpdatePollVote
{
    /**
     * Toggle the user's vote for the given poll option.
     */
    public function handle(User $user, Question $question, PollOption $pollOption): void
    {
        DB::transaction(function () use ($user, $question, $pollOption): void {
            /** @var PollVote|null $existingVote */
            $existingVote = PollVote::query()
                ->where('user_id', $user->id)
                ->where('question_id', $question->id)
                ->lockForUpdate()
                ->first();

            if ($existingVote !== null) {
                $existingVote->pollOption->decrement('votes_count');
                $existingVote->delete();

                if ($existingVote->poll_option_id === $pollOption->id) {
                    return;
                }
            }

            try {
                PollVote::create([
                    'user_id' => $user->id,
                    'poll_option_id' => $pollOption->id,
                    'question_id' => $question->id,
                ]);
            } catch (UniqueConstraintViolationException) {
                // A concurrent first-time vote won the race; the vote
                // already exists, so treat this request as idempotent.
                return;
            }

            $pollOption->increment('votes_count');
        });
    }
}
