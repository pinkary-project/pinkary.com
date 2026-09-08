<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Actions\Questions\CreateBookmark;
use App\Actions\Questions\CreateLike;
use App\Actions\Questions\DeleteBookmark;
use App\Actions\Questions\DeleteLike;
use App\Actions\Questions\UpdateQuestionPin;
use App\Actions\Questions\UpdateQuestionStatus;
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
     * Render a bottom border for this post row.
     */
    #[Locked]
    public bool $showBorder = false;

    /**
     * Determine if this is currently being viewed in a channel feed.
     */
    #[Locked]
    public bool $inChannel = false;

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
    public function ignore(UpdateQuestionStatus $updateQuestionStatus): void
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

        $updateQuestionStatus->handle($question, ignored: true);

        $this->redirectRoute('profile.show', ['username' => $question->to->username], navigate: true);
    }

    /**
     * Bookmark the question.
     */
    #[Renderless]
    public function bookmark(CreateBookmark $createBookmark): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        /** @var User $user */
        $user = auth()->user();

        $bookmark = $createBookmark->handle($question, $user);

        if ($bookmark->wasRecentlyCreated) {
            $this->dispatch('notification.created', message: 'Bookmark added.');
        }
    }

    /**
     * Like the question.
     */
    #[Renderless]
    public function like(CreateLike $createLike): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        /** @var User $user */
        $user = auth()->user();

        $createLike->handle($question, $user);
    }

    /**
     * Pin a question.
     */
    public function pin(#[CurrentUser] ?User $user, UpdateQuestionPin $updateQuestionPin): void
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

        $updateQuestionPin->handle($user, $question, true);

        $this->dispatch('question.updated');
    }

    /**
     * Unpin a pinned question.
     */
    public function unpin(#[CurrentUser] ?User $user, UpdateQuestionPin $updateQuestionPin): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $updateQuestionPin->handle($user, $question, false);

        $this->dispatch('question.updated');
    }

    /**
     * Unbookmark the question.
     */
    #[Renderless]
    public function unbookmark(DeleteBookmark $deleteBookmark): void
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

            if ($deleteBookmark->handle($bookmark)) {
                $this->dispatch('notification.created', message: 'Bookmark removed.');
            }
        }

        $this->dispatch('question.unbookmarked');
    }

    /**
     * Unlike the question.
     */
    #[Renderless]
    public function unlike(DeleteLike $deleteLike): void
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

            $deleteLike->handle($like);
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
