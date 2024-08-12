<?php

declare(strict_types=1);

namespace App\Livewire\Answers;

use App\Models\Answer;
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
        $question = Question::with('answer')->findOrFail($questionId);
        /** @var Answer $answer */
        $answer = $question->getRelation('answer');
        $rawContent = $answer->getRawOriginal('content');
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
            ->with('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->find($this->questionId);

        if ($question === null) {
            $this->dispatch('notification.created', message: 'Sorry, something unexpected happened. Please try again.');
            $this->redirectRoute('profile.show', ['username' => $user->username], navigate: true);

            return;
        }

        $this->authorize('update', $question);

        if ($question->answer && $question->answer->created_at->diffInHours(now()) > 24) {
            $this->dispatch('notification.created', message: 'Answer cannot be edited after 24 hours.');

            return;
        }

        $answer = Answer::updateOrCreate(
            ['question_id' => $this->questionId],
            ['content' => $validated['content']],
        );

        if ($answer->wasChanged('content')) {
            $question->likes()->delete();
            $this->dispatch('close-modal', "question.edit.answer.{$this->questionId}");
        }

        $message = match (true) {
            $answer->wasChanged('content') => 'Answer updated.',
            default => 'Question answered.',
        };

        $this->dispatch('notification.created', message: $message);
        $this->dispatch('question.updated');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.answers.edit', [
            'question' => Question::with('answer')->findOrFail($this->questionId),
            'user' => $request->user(),
        ]);
    }
}
