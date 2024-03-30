<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Http\Request;
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
            $user = $request->user();
            assert($user instanceof User);

            $this->anonymously = $user->anonymously_preference;
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
            redirect()->route('login');

            return;
        }

        $user = $request->user();

        assert($user instanceof User);

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
            'anonymously' => ['boolean'],
            'content' => ['required', 'string', 'max:255', new NoBlankCharacters],
        ]);

        $user->questionsSent()->create([
            ...$validated,
            'to_id' => $this->toId,
        ]);

        $this->reset(['content', 'anonymously']);

        $this->dispatch('question.created');
        $this->dispatch('notification.created', 'Question sent.');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = User::findOrFail($this->toId);

        return view('livewire.questions.create', [
            'user' => $user,
        ]);
    }
}
