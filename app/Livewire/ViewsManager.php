<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\IncrementViews;
use App\Models\Question;
use Illuminate\Support\Collection;
use Livewire\Component;

final class ViewsManager extends Component
{
    /**
     * Send the viewed posts to the job.
     *
     * @param  array<array-key, string>  $postIds
     */
    public function updateViews(array $postIds): void
    {
        $collection = new Collection();
        foreach ($postIds as $postId) {
            $collection->push((new Question())->setRawAttributes(['id' => $postId]));
        }

        IncrementViews::dispatchUsingSession($collection);
    }

    /**
     * Render the component.
     */
    public function render(): string
    {
        return <<<'HTML'
            <div x-data="viewManager"></div>
        HTML;
    }
}
