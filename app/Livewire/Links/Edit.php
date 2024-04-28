<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Edit extends Component
{
    /**
     * The component's link ID.
     */
    #[Locked]
    public int $linkId;

    /**
     * The component's link ID.
     */
    public ?Link $link;

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
    public function update(Request $request): void
    {
        $user = type($request->user())->as(User::class);

        if (! Str::startsWith($this->url, ['http://', 'https://'])) {
            $this->url = "https://{$this->url}";
        }

        $validated = $this->validate([
            'description' => 'required|max:100',
            'url' => ['required', 'max:100', 'url', 'starts_with:https'],
        ]);

        $this->authorize('update', $this->link);

        if ($this->link->url !== $validated['url']) {
            $validated['click_count'] = 0;
        }

        $this->link->update($validated);

        $this->dispatch('link.updated');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        return view('livewire.links.edit', [
            'user' => $request->user(),
        ]);
    }

    #[On('link.edit')]
    public function edit(int $linkId): void
    {
        $this->link = Link::findOrFail($linkId);

        $this->description = $this->link->description;
        $this->url = $this->link->url;
    }
}
