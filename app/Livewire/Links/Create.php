<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

final class Create extends Component
{
    /**
     * The component's description.
     */
    public string $description = '';

    /**
     * The component's URL.
     */
    public string $url = '';

    /**
     * Store a new link.
     */
    public function store(#[CurrentUser] User $user): void
    {
        $linksCount = $user->links()->count();

        if ($linksCount >= 10 && ! $user->is_verified) {
            $this->addError('url', 'You can only have 10 links at a time.');

            return;
        }

        if ($linksCount >= 20 && $user->is_verified) {
            $this->addError('url', 'You can only have 20 links at a time.');

            return;
        }

        if (! Str::startsWith($this->url, ['http://', 'https://'])) {
            $this->url = "https://{$this->url}";
        }

        /** @var array<string, string> $validated */
        $validated = $this->validate([
            'description' => 'required|max:100',
            'url' => ['required', 'max:100', 'url', 'starts_with:https'],
        ]);

        $user->links()->create($validated);

        $this->description = '';
        $this->url = '';

        $this->dispatch('link.created');
        $this->dispatch('notification.created', message: 'Link created.');
    }

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        return view('livewire.links.create', [
            'user' => $user,
        ]);
    }
}
