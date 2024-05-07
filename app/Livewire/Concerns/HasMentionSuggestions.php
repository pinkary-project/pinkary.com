<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait HasMentionSuggestions
{
    /**
     * The component's mention suggestions search query.
     */
    public string $mentionSuggestionsQuery = '';

    /**
     * Search for a user by username.
     *
     * @return Collection<int, User>
     */
    public function mentionSuggestions(): Collection
    {
        return User::query()
            ->where('username', 'like', "%{$this->mentionSuggestionsQuery}%")
            ->limit(10)
            ->get();
    }
}
