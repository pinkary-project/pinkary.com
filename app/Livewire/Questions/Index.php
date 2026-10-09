<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Actions\Questions\UpdateQuestionStatus;
use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use App\Queries\Feeds\UserQuestionsFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Index extends Component
{
    use HasLoadMore;

    /**
     * The component's user ID.
     */
    #[Locked]
    public int $userId;

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = User::findOrFail($this->userId);

        $pinnedQuestion = $user->questionsReceived()
            ->tap(new WhereNotModerated)
            ->where('pinned', true)
            ->first();

        $questions = new UserQuestionsFeed($user, $request->user()?->id)
            ->builder(includePinned: false)
            ->simplePaginate($this->perPage);

        return view('livewire.questions.index', [
            'user' => $user,
            'questions' => $questions,
            'pinnedQuestion' => $pinnedQuestion,
        ]);
    }

    /**
     * Refresh the component.
     */
    #[On('question.created')]
    #[On('question.updated')]
    #[On('question.reported')]
    #[On('question.reposted')]
    #[On('question.unreposted')]
    public function refresh(): void {}

    /**
     * Ignore the given question.
     */
    #[On('question.ignore')]
    public function ignore(UpdateQuestionStatus $updateQuestionStatus, string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $updateQuestionStatus->handle($question, ignored: true);

        $this->dispatch('question.ignored');
    }
}
