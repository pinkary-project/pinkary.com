<?php

declare(strict_types=1);

namespace App\Livewire\LinkSettings;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
use Illuminate\View\View;
use Livewire\Component;

final class Edit extends Component
{
    /**
     * The component's link shape.
     */
    public string $link_shape = '';

    /**
     * The component's gradient.
     */
    public string $gradient = '';

    /**
     * Mount the component.
     */
    public function mount(Request $request): void
    {
        $user = type($request->user())->as(User::class);

        $this->link_shape = $user->link_shape;
        $this->gradient = $user->gradient;
    }

    /**
     * Update the user's link settings.
     */
    public function update(Request $request): void
    {
        $user = type($request->user())->as(User::class);

        $validated = $this->validate([
            'link_shape' => 'required|in:rounded-none,rounded-lg,rounded-full',
            'gradient' => [
                'required', new In([
                    'from-blue-500 to-purple-600',
                    'from-blue-500 to-teal-700',
                    'from-red-500 to-orange-600',
                    'from-purple-500 to-pink-500',
                    'from-indigo-500 to-lime-700',
                    'from-yellow-600 to-blue-600',
                ]),
            ],
        ]);

        $user->update(['settings' => $validated]);

        $this->dispatch('link-settings.updated');
        $this->dispatch('notification.created', message: 'Link settings updated.');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.link-settings.edit',
            ['user' => $request->user()]
        );
    }
}
