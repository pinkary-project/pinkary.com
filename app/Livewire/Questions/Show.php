<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Question;
use App\Models\User;
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
     * Determine if the question is a repost.
     */
    #[Locked]
    public bool $isRepost = false;

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

        if ($this->doesNotHaveVerifiedEmail()) {
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
     * Repost the question.
     */
    #[Renderless]
    public function repost(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $question = Question::findOrFail($this->questionId);
        $user = type(auth()->user())->as(User::class);

        $questionClone = $question->replicate();
        $questionClone->is_repost = true;
        $questionClone->from_id = $user->id;
        $questionClone->to_id = $user->id;
        $questionClone->root_id = null;
        $questionClone->parent_id = $question->id;

        $questionClone->save();

        $this->dispatch('question.created');

        $message = 'Question reposted.';

        $this->dispatch('notification.created', message: $message);
    }

    /**
     * unRepost the question.
     */
    #[Renderless]
    public function unRepost(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        /**
         * @var Question $question
         */
        $question = Question::query()
            ->where('parent_id', $this->questionId)
            ->where('from_id', auth()->id())
            ->where('is_repost', true)
            ->firstOrFail();

        $question->delete();

        $message = 'Question un-reposted.';

        $this->dispatch('notification.created', message: $message);
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

        if ($this->doesNotHaveVerifiedEmail()) {
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
        $question = Question::query()->where('id', $this->questionId)
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
