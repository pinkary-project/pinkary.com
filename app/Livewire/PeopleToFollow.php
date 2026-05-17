<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\PeopleToFollowRecommendations;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PeopleToFollow extends Component
{
    /**
     * The current page context for the widget.
     */
    #[Locked]
    public string $context = 'generic';

    /**
     * The contextual user ID.
     */
    #[Locked]
    public ?int $contextUserId = null;

    /**
     * The contextual question ID.
     */
    #[Locked]
    public ?string $contextQuestionId = null;

    /**
     * Render the component.
     */
    public function render(PeopleToFollowRecommendations $recommendations): View
    {
        $authenticatedUser = auth()->user();

        return view('livewire.people-to-follow', [
            'users' => $recommendations->forContext(
                authenticatedUserId: $authenticatedUser instanceof User ? $authenticatedUser->id : null,
                context: $this->context,
                contextUserId: $this->contextUserId,
                contextQuestionId: $this->contextQuestionId,
            ),
        ]);
    }
}
