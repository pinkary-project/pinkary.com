<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\User;
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
    public string $content = '';

    /**
     * Mount the component.
     */
    public function mount(string $questionId): void
    {
        $this->questionId = $questionId;
        $question = Question::findOrFail($questionId);
        $rawContent = $question->getRawOriginal('content');
        $this->content = is_string($rawContent) ? $rawContent : '';
    }

    /**
     * Updates the question with the given answer.
     */
    public function update(Request $request): void
    {
        $validated = $this->validate([
            'content' => ['required', 'string', 'max:1000', new NoBlankCharacters],
        ]);

        $user = type($request->user())->as(User::class);

        $question = Question::query()
            ->whereDoesntHave('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->find($this->questionId);

        if ($question === null) {
            $this->dispatch('notification.created', message: 'Sorry, something unexpected happened. Please try again.');
            $this->redirectRoute('profile.show', ['username' => $user->username], navigate: true);

            return;
        }

        if ($question->isSharedUpdate() && $question->created_at->diffInHours(now()) > 24) {
            $this->dispatch('notification.created', message: 'Posts cannot be edited after 24 hours.');

            return;
        }

        $this->authorize('update', $question);

        $question->content = $validated['content'];
        $question->is_update = $question->isSharedUpdate();

        if ($question->isDirty('content')) {
            $question->likes()->delete();
            $this->dispatch('close-modal', "question.edit.update.{$question->id}");
        }

        $message = match (true) {
            $question->isDirty('content') => 'Update Edited.',
            default => 'Update shared.',
        };

        $question->save();

        $this->dispatch('notification.created', message: $message);
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

        $this->dispatch('notification.created', message: 'Question reported.');
        $this->dispatch('question.reported');
    }

    /**
     * Ignores the question.
     */
    public function ignore(): void
    {
        $this->dispatch('notification.created', message: 'Question ignored.');

        $this->dispatch('question.ignore', questionId: $this->questionId);
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
