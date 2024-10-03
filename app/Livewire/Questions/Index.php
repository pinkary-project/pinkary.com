<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $questions = $user
            ->questionsReceived()
            ->select('id', 'root_id', 'parent_id')
            ->withExists([
                'root as showRoot' => function (Builder $query) use ($user): void {
                    $query->where('to_id', $user->id);
                },
                'parent as showParent' => function (Builder $query) use ($user): void {
                    $query->where('to_id', $user->id);
                },
            ])
            ->with('parent:id,parent_id')
            ->where('pinned', false)
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->when($user->isNot($request->user()), function (Builder|HasMany $query): void {
                $query->whereNotNull('answer');
            })
            ->where(function (Builder|HasMany $query): void {
                $query->whereNull('parent_id')->orWhere('showParent', true)->orWhere('showRoot', true);
            })
            ->groupBy(DB::Raw('IFNULL(root_id, id)'))
            ->orderByDesc(DB::raw('MAX(`updated_at`)'))
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
