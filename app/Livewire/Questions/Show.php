<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read Question $question
 */
final class Show extends Component
{
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
     * Whether the pinned label should be displayed or not.
     */
    #[Locked]
    public bool $pinnable = false;

    private ?Question $questionFromParent;

    /**
     * Mount the component.
     */
    public function mount(?Question $question = null): void
    {
        $this->questionFromParent = $question;
    }

    /**
     * Get the question.
     */
    #[Computed()]
    public function question(): Question
    {
        return $this->questionFromParent ?? Question::findOrFail($this->questionId);
    }

    /**
     * Refresh the component.
     */
    #[On('question.updated')]
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
        $question = $this->question;

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

        $question = $this->question;

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

        $question = $this->question;

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

        $question = $this->question;

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

        $question = $this->question;

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

        $question = $this->question;

        if ($like = $question->likes()->where('user_id', auth()->id())->first()) {
            $this->authorize('delete', $like);

            $like->delete();
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $question = $this->question;

        return view('livewire.questions.show', [
            'user' => $question->to,
            'question' => $question,
        ]);
    }
}
