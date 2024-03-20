<?php

declare(strict_types=1);

namespace App\Livewire\Updates;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class Create extends Component
{
    /**
     * The component's answer.
     */
    public string $answer = '';

    /**
     * Stores a new question.
     */
    public function store(Request $request): void
    {
        $user = $request->user();

        assert($user instanceof User);

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'answer' => ['required', 'string', 'max:1000'],
        ]);

        $user->questionsSent()->create([
            ...$validated,
            'anonymously' => false,
            'answered_at' => now(),
            'content' => '',
            'from_id' => $user->id,
            'to_id' => $user->id,
        ]);

        $this->reset(['answer']);

        $this->dispatch('notification.created', 'Update shared.');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.updates.create', [
            'user' => $request->user(),
        ]);
    }
}
