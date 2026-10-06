<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Actions\Users\UpdateUser;
use App\Enums\UserQuestionPreference;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

final class QuestionPreferences extends Component
{
    /**
     * The selected question preference.
     */
    public string $questionPreference = '';

    /**
     * Mount the component.
     */
    public function mount(#[CurrentUser] User $user): void
    {
        $this->questionPreference = $user->question_preference->value;
    }

    /**
     * Open the dialog with the saved preference.
     */
    public function edit(#[CurrentUser] User $user): void
    {
        $this->authorize('updateQuestionPreference', $user);
        $this->resetValidation();
        $this->questionPreference = $user->refresh()->question_preference->value;
        $this->dispatch('open-modal', 'question-preferences');
    }

    /**
     * Save the user's question preference.
     */
    public function update(#[CurrentUser] User $user, UpdateUser $updateUser): void
    {
        $this->authorize('updateQuestionPreference', $user);
        $this->validate(['questionPreference' => ['required', Rule::enum(UserQuestionPreference::class)]]);

        $updateUser->handle($user, ['question_preference' => $this->questionPreference]);

        $this->dispatch('question-preference.updated');
        $this->dispatch('close-modal', 'question-preferences');
        $this->dispatch('notification.created', message: 'Question preference updated.');
    }

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        return view('livewire.profile.question-preferences', [
            'user' => $user,
            'options' => UserQuestionPreference::toArray(),
        ]);
    }
}
