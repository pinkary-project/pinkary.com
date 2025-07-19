<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Question;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
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
    #[Renderless]
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
        $pollOption = PollOption::where('question_id', $question->id)
            ->findOrFail($pollOptionId);

        // Check if user already voted on this poll
        $existingVote = PollVote::whereHas('pollOption', function ($query) use ($question): void {
            $query->where('question_id', $question->id);
        })->where('user_id', $user->id)->first();

        if ($existingVote) {
            // Remove existing vote
            $existingVote->pollOption->decrement('votes_count');
            $existingVote->delete();

            // If voting for the same option, just remove the vote (toggle off)
            if ($existingVote->poll_option_id === $pollOptionId) {
                $this->dispatch('poll.voted');
                return;
            }
        }

        // Add new vote
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
        $question = Question::with(['pollOptions' => function ($query): void {
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
        ]);
    }
}
