<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->where('pinned', true)
            ->first();

        $latestQuestions = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->where('to_id', $user->id)
            ->when($user->isNot($request->user()), function (Builder $query): void {
                $query->whereNotNull('answer');
            });

        $questions = $user
            ->questionsReceived()
            ->select('questions.id', 'questions.root_id', 'questions.parent_id')
            ->joinSub(
                $latestQuestions,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->withExists([
                'root as showRoot' => function (Builder $query) use ($user): void {
                    $query->where('to_id', $user->id);
                },
                'parent as showParent' => function (Builder $query) use ($user): void {
                    $query->where('to_id', $user->id);
                },
            ])
            ->with('parent:id,parent_id')
            ->where('grouped_questions.thread_rank', 1)
            ->where('questions.pinned', false)
            ->where('questions.is_reported', false)
            ->where('questions.is_ignored', false)
            ->when($user->isNot($request->user()), function (Builder|HasMany $query): void {
                $query->whereNotNull('questions.answer');
            })
            ->where(function (Builder $query) use ($user): void {
                $belongsToUser = function (Builder $query) use ($user): void {
                    $query->where('to_id', $user->id);
                };

                $query->whereNull('questions.parent_id')
                    ->orWhereHas('root', $belongsToUser)
                    ->orWhereHas('parent', $belongsToUser);
            })
            ->orderByDesc('grouped_questions.last_update')
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
    public function refresh(): void {}

    /**
     * Ignore the given question.
     */
    #[On('question.ignore')]
    public function ignore(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->dispatch('question.ignored');
    }
}
