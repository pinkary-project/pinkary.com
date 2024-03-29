<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Jobs\DownloadUserAvatar;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function store(Request $request): void
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->links()->count() >= 10 && !$user->is_verified) {
            $this->addError('url', 'You can only have 10 links at a time.');

            return;
        }

        if ($user->links()->count() >= 20 && $user->is_verified) {
            $this->addError('url', 'You can only have 20 links at a time.');

            return;
        }

        if (!Str::startsWith($this->url, ['http://', 'https://'])) {
            $this->url = "https://{$this->url}";
        }

        if(!$this->isLinkActive($this->url)) {
            $this->addError('url', 'The link appears to be broken.');

            return;
        }

        $validated = $this->validate([
            'description' => 'required|max:100',
            'url' => 'required|max:100|url|starts_with:https',
        ]);

        $user->links()->create($validated);

        dispatch(new DownloadUserAvatar($user));

        $this->description = '';
        $this->url = '';

        $this->dispatch('link.created');
        $this->dispatch('notification.created', 'Link created.');
    }

    /**
     * Check if the given link is active.
     */
    private function isLinkActive($url): bool
    {
        $file_headers = @get_headers($url);
        if (!$file_headers || $file_headers[0] == "HTTP/1.1 404 Not Found") {
            return false;
        } 

        return true;
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
