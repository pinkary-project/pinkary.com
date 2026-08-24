<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Question;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Show extends Component
{
    use NeedsVerifiedEmail;

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
     * Redirect to the profile.
     */
    #[On('question.reported')]
    public function redirectToProfile(?string $questionId = null): void
    {
        if ($questionId !== null && $questionId !== $this->questionId) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->redirectRoute('profile.show', ['username' => $question->to->username], navigate: true);
    }

    /**
     * Ignores the question.
     */
    #[On('question.ignore')]
    public function ignore(?string $questionId = null): void
    {
        if ($questionId !== null && $questionId !== $this->questionId) {
            return;
        }

        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('ignore', $question);

        $affectedIds = $question->idsAffectedByIgnore();

        $question->update(['is_ignored' => true]);

        $this->dispatch('notification.created', message: 'Question ignored.');

        foreach ($affectedIds as $affectedId) {
            $this->dispatch('question.ignored', questionId: $affectedId);
        }
    }

    /**
     * Bookmark the question.
     */
    #[Renderless]
    public function bookmark(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        $bookmark = $question->bookmarks()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        if ($bookmark->wasRecentlyCreated) {
            $this->dispatch('notification.created', message: 'Bookmark added.');
        }
    }

    /**
     * Like the question.
     */
    #[Renderless]
    public function like(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
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
    public function pin(#[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

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

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        Question::withoutTimestamps(fn () => $question->update(['pinned' => false]));

        $this->dispatch('question.updated');
    }

    /**
     * Unbookmark the question.
     */
    #[Renderless]
    public function unbookmark(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        if ($bookmark = $question->bookmarks()->where('user_id', auth()->id())->first()) {
            $this->authorize('delete', $bookmark);

            if ($bookmark->delete()) {
                $this->dispatch('notification.created', message: 'Bookmark removed.');
            }
        }

        $this->dispatch('question.unbookmarked');
    }

    /**
     * Unlike the question.
     */
    #[Renderless]
    public function unlike(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
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
    public function placeholder(): View
    {
        return view('livewire.questions.placeholder'); // @codeCoverageIgnore
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $question = Question::where('id', $this->questionId)
            ->with(['to', 'from'])
            ->withExists(['bookmarks as is_bookmarked' => function (Builder $query): void {
                $query->where('user_id', auth()->id());
            }, 'likes as is_liked' => function (Builder $query): void {
                $query->where('user_id', auth()->id());
            }])
            ->when(! $this->inThread || $this->commenting, function (Builder $query): void {
                $query->with('parent');
            })
            ->withCount(['likes', 'children', 'bookmarks'])
            ->firstOrFail();

        return view('livewire.questions.show', [
            'user' => $question->to,
            'question' => $question,
        ]);
    }
}
