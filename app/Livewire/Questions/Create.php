<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Create extends Component
{
    /**
     * The component's user ID.
     */
    #[Locked]
    public int $toId;

    /**
     * The component's content.
     */
    public string $content = '';

    /**
     * The component's anonymously state.
     */
    public bool $anonymously = true;

    /**
     * Mount the component.
     */
    public function mount(Request $request): void
    {
        if (auth()->check()) {
            $user = type($request->user())->as(User::class);

            $this->anonymously = $user->prefers_anonymous_questions;
        }
    }

    /**
     * Refresh the component.
     */
    #[On('link-settings.updated')]
    public function refresh(): void
    {
        //
    }

    /**
     * Stores a new question.
     */
    public function store(Request $request): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type($request->user())->as(User::class);

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            $this->addError('content', 'You can only send 3 questions per minute.');

            return;
        }

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subDay())->count() > 30) {
            $this->addError('content', 'You can only send 30 questions per day.');

            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'anonymously' => ['boolean', Rule::excludeIf($user->id === $this->toId)],
            'content' => ['required', 'string', 'max:255', new NoBlankCharacters],
        ]);

        $validated['anonymously'] ??= false;

        $user->questionsSent()->create([
            ...$validated,
            'to_id' => $this->toId,
        ]);

        $this->reset(['content']);

        $this->anonymously = $user->prefers_anonymous_questions;

        $this->dispatch('question.created');
        $this->dispatch('notification.created', message: 'Question sent.');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $user = User::findOrFail($this->toId);

        return view('livewire.questions.create', [
            'user' => $user,
        ]);
    }
}
