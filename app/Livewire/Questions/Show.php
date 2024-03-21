<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

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
     * Refresh the component.
     */
    #[On('question.updated')]
    #[On('question.pinned')]
    #[On('question.unpinned')]
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
            'question.destroy' => 'destroy',
            'question.reported' => 'redirectToProfile',
        ];
    }

    /**
     * Redirect to the profile.
     */
    public function redirectToProfile(): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->redirect(route('profile.show', ['user' => $question->to->username]));
    }

    /**
     * Destroy the question.
     */
    public function destroy(): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->authorize('delete', $question);

        $question->delete();

        $this->redirect(route('profile.show', ['user' => $question->to->username]));
    }

    /**
     * Like the question.
     */
    public function like(): void
    {
        if (! auth()->check()) {
            redirect()->route('login');

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
            redirect()->route('login');

            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('pin', $question);

        $question->pinned = true;
        $question->save();

        $this->dispatch('question.pinned');
    }

    /**
     * Unpin a pinned question.
     */
    public function unpin(): void
    {
        if (! auth()->check()) {
            redirect()->route('login');

            return;
        }

        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $question->pinned = false;
        $question->save();

        $this->dispatch('question.unpinned');
    }

    /**
     * Unlike the question.
     */
    public function unlike(): void
    {
        if (! auth()->check()) {
            redirect()->route('login');

            return;
        }

        $question = Question::findOrFail($this->questionId);

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
        $question = Question::findOrFail($this->questionId);

        return view('livewire.questions.show', [
            'user' => $question->to,
            'question' => $question,
        ]);
    }
}
