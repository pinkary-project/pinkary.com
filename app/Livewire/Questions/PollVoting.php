<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PollVoting extends Component
{
    use NeedsVerifiedEmail;

    /**
     * The question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * Vote for a poll option.
     */
    public function vote(int $pollOptionId, #[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        if ($question->isPollExpired()) {
            $this->addError('poll', 'This poll has expired and voting is no longer allowed.');

            return;
        }

        $pollOption = PollOption::where('question_id', $question->id)
            ->findOrFail($pollOptionId);

        $existingVote = PollVote::whereHas('pollOption', function ($query) use ($question): void {
            $query->where('question_id', $question->id);
        })->where('user_id', $user->id)->first();

        if ($existingVote) {
            $existingVote->pollOption->decrement('votes_count');
            $existingVote->delete();

            if ($existingVote->poll_option_id === $pollOptionId) {
                $this->dispatch('poll.voted');

                return;
            }
        }

        PollVote::create([
            'user_id' => $user->id,
            'poll_option_id' => $pollOptionId,
        ]);

        $pollOption->increment('votes_count');

        $this->dispatch('poll.voted');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $question = Question::with(['pollOptions' => function (Builder|HasMany $query): void {
            $query->orderBy('id');
        }])->findOrFail($this->questionId);

        $userVote = null;
        if (auth()->check()) {
            $userVote = PollVote::whereHas('pollOption', function ($query) use ($question): void {
                $query->where('question_id', $question->id);
            })->where('user_id', auth()->id())->first();
        }

        $totalVotes = $question->pollOptions->sum('votes_count');

        return view('livewire.questions.poll-voting', [
            'question' => $question,
            'pollOptions' => $question->pollOptions,
            'userVote' => $userVote,
            'totalVotes' => $totalVotes,
            'isPollExpired' => $question->isPollExpired(),
            'timeRemaining' => $question->getPollTimeRemaining(),
        ]);
    }
}
