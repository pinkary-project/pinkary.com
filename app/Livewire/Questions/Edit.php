<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Rules\NoBlankCharacters;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Edit extends Component
{
    /**
     * The component's question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * The component's answer.
     */
    public string $answer = '';

    /**
     * Updates the question with the given answer.
     */
    public function update(): void
    {
        $this->validate([
            'answer' => ['required', 'string', 'max:1000', new NoBlankCharacters],
        ]);

        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $question->update([
            'answer' => $this->answer,
            'answered_at' => now(),
        ]);

        $this->answer = '';

        $this->dispatch('notification.created', 'Question answered.');
        $this->dispatch('question.updated');
    }

    /**
     * Reports the question.
     */
    public function report(): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $question->update([
            'is_reported' => true,
        ]);

        $this->dispatch('notification.created', 'Question reported.');
        $this->dispatch('question.reported');
    }

    /**
     * Destroys / Ignores the question.
     */
    public function destroy(): void
    {
        $this->dispatch('notification.created', 'Question ignored.');

        $this->dispatch('question.destroy', questionId: $this->questionId);
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.questions.edit', [
            'question' => Question::findOrFail($this->questionId),
            'user' => $request->user(),
        ]);
    }
}
