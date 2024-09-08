<?php

declare(strict_types=1);

namespace App\Livewire\Views;

use App\Jobs\IncrementViews;
use App\Models\Question;
use Livewire\Attributes\Renderless;
use Livewire\Component;

final class Create extends Component
{
    /**
     * Send the viewed posts to the job.
     *
     * @param  array<array-key, string>  $postIds
     */
    #[Renderless]
    public function store(array $postIds): void
    {
        $questions = collect($postIds)->map(fn (string $postId): Question => (new Question())->setRawAttributes(['id' => $postId]));

        IncrementViews::dispatchUsingSession($questions);
    }

    /**
     * Render the component.
     */
    public function render(): string
    {
        return <<<'HTML'
            <div x-data="viewCreate"></div>
        HTML;
    }
}
