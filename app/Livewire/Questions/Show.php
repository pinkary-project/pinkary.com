<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Show extends Component
{
    /**
     * The component's question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * Determine if this is currently being viewed in the index (list) view.
     */
    #[Locked]
    public bool $inIndex = false;

    /**
     * Determine if this is currently being viewed in thread view.
     */
    #[Locked]
    public bool $inThread = false;

    /**
     * Whether the pinned label should be displayed or not.
     */
    #[Locked]
    public bool $pinnable = false;

    /**
     * Enable the comment box.
     */
    #[Locked]
    public bool $commenting = false;

    /**
     * The previous question ID, where the user came from.
     */
    #[Url]
    public ?string $previousQuestionId = null;

    /**
     * Refresh the component.
     */
    #[On('question.updated')]
    #[On('question.created')]
    public function refresh(): void
    {
        //
    }

    /**
     * Get the listeners for the component.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return $this->inIndex ? [] : [
            'question.ignore' => 'ignore',
            'question.reported' => 'redirectToProfile',
        ];
    }

    /**
     * Redirect to the profile.
     */
    public function redirectToProfile(): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->redirectRoute('profile.show', ['username' => $question->to->username], navigate: true);
    }

    /**
     * Ignores the question.
     */
    public function ignore(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->inIndex) {
            $this->dispatch('notification.created', message: 'Question ignored.');

            $this->dispatch('question.ignore', questionId: $this->questionId);

            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->redirectRoute('profile.show', ['username' => $question->to->username], navigate: true);
    }

    /**
     * Like the question.
     */
    public function like(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $question = Question::findOrFail($this->questionId);

        $question->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Pin a question.
     */
    public function pin(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type(auth()->user())->as(User::class);

        $question = Question::findOrFail($this->questionId);

        $this->authorize('pin', $question);

        Question::withoutTimestamps(fn () => $user->pinnedQuestion()->update(['pinned' => false]));
        Question::withoutTimestamps(fn () => $question->update(['pinned' => true]));

        $this->dispatch('question.updated');
    }

    /**
     * Unpin a pinned question.
     */
    public function unpin(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        Question::withoutTimestamps(fn () => $question->update(['pinned' => false]));

        $this->dispatch('question.updated');
    }

    /**
     * Unlike the question.
     */
    public function unlike(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $question = Question::findOrFail($this->questionId);

        if ($like = $question->likes()->where('user_id', auth()->id())->first()) {
            $this->authorize('delete', $like);

            $like->delete();
        }
    }

    /**
     * Get the placeholder for the component.
     */
    public function placeholder(): string
    {
        return <<<'HTML'
            <article class="block">
                <div class="animate-pulse group p-4 mt-3 rounded-2xl bg-slate-900">
                    <div class="flex items center justify-between">
                        <div class="flex items center w-full">
                            <div class="flex-shrink-0 mr-3">
                                <div class="w-10 h-10 bg-slate-700 rounded-full"></div>
                            </div>
                            <div class="flex flex-col">
                                <div class="w-20 h-2 mt-2 bg-slate-700 rounded"></div>
                                <div class="w-16 h-2 mt-2.5 bg-slate-700 rounded"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-column justify-start w-full py-1">
                        <div class="h-2 w-10/12 bg-slate-700 my-3 rounded"></div>
                        <div class="h-2 bg-slate-700 my-3 rounded"></div>
                        <div class="h-2 w-32 bg-slate-700 my-3 rounded"></div>
                    </div>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-start">
                            <div class="w-4 h-4 bg-slate-700 rounded-full"></div>
                            <div class="w-4 h-4 bg-slate-700 rounded-full ml-2"></div>
                            <div class="w-4 h-4 bg-slate-700 rounded-full ml-2"></div>
                        </div>
                        <div class="flex items-end">
                            <div class="w-16 h-2 my-1 bg-slate-700 rounded"></div>
                            <div class="w-4 h-4 bg-slate-700 rounded-full ml-2"></div>
                        </div>
                    </div>
                </div>
            </article>
        HTML;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $question = Question::where('id', $this->questionId)
            ->with(['to', 'from', 'likes'])
            ->when(! $this->inThread || $this->commenting, function (Builder $query): void {
                $query->with('parent');
            })
            ->when($this->inThread, function (Builder $query): void {
                $query->with(['children']);
            })
            ->withCount(['likes', 'children'])
            ->firstOrFail();

        return view('livewire.questions.show', [
            'user' => $question->to,
            'question' => $question,
        ]);
    }
}
