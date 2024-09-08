<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

final class Create extends Component
{
    /**
     * The component's title.
     */
    public string $title = '';

    /**
     * The component's URL.
     */
    public string $url = '';

    /**
     * Store a new link.
     */
    public function store(Request $request): void
    {
        $user = type($request->user())->as(User::class);

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

        $validated = $this->validate([
            'title' => 'required|max:100',
            'url' => ['required', 'max:100', 'url', 'starts_with:https'],
        ]);

        $user->links()->create($validated);

        $this->title = '';
        $this->url = '';

        $this->dispatch('link.created');
        $this->dispatch('notification.created', message: 'Link created.');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.links.create', [
            'user' => $request->user(),
        ]);
    }
}
