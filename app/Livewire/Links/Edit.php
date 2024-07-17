<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Models\Link;
use Illuminate\Auth\Access\AuthorizationException;
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
    public ?int $linkId = null;

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
     *
     * @throws AuthorizationException
     */
    public function update(Request $request): void
    {
        if (! Str::startsWith($this->url, ['http://', 'https://'])) {
            $this->url = "https://{$this->url}";
        }

        $validated = $this->validate([
            'description' => 'required|max:100',
            'url' => ['required', 'max:100', 'url', 'starts_with:https'],
        ]);

        $link = Link::findOrFail($this->linkId);

        $this->authorize('update', $link);

        if ($link->url !== $validated['url']) {
            $validated['click_count'] = 0;
        }

        $link->update($validated);

        $this->dispatch('link.updated');
        $this->dispatch('close-modal', 'link-edit-modal');
        $this->dispatch('notification.created', message: 'Link updated.');
    }

    /**
     * Initialize the edit link form component.
     */
    #[On('link.edit')]
    public function edit(Link $link): void
    {
        $this->linkId = $link->id;
        $this->description = $link->description;
        $this->url = $link->url;
        $this->dispatch('open-modal', 'link-edit-modal');
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
}
