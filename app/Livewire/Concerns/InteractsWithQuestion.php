<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question;
use App\Models\User;
use Livewire\Attributes\Locked;

trait InteractsWithQuestion
{
    public Question $question2;

    /**
     * The component's question ID.
     */
    #[Locked]
    public string $questionId;
    
    /**
     * The component's in index state.
     */
    #[Locked]
    public bool $inIndex = false;

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

        $this->redirect(route('profile.show', ['username' => $question->to->username]));
    }

    /**
     * Ignores the question.
     */
    public function ignore(): void
    {
        if (! auth()->check()) {
            to_route('login');

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

        $this->redirect(route('profile.show', ['username' => $question->to->username]));
    }

    /**
     * Like the question.
     */
    public function like(): void
    {
        if (! auth()->check()) {
            to_route('login');

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
            to_route('login');

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
            to_route('login');

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
            to_route('login');

            return;
        }

        $question = Question::findOrFail($this->questionId);

        if ($like = $question->likes()->where('user_id', auth()->id())->first()) {
            $this->authorize('delete', $like);

            $like->delete();
        }
    }
}
