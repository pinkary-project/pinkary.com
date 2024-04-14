<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Locked;

trait HasLoadMore
{
    /**
     * The component's amount of questions per page.
     */
    #[Locked]
    public int $perPage = 5;

    /**
     * Load more questions.
     */
    public function loadMore(): void
    {
        $this->perPage = $this->perPage > 100 ? 100 : ($this->perPage + 5);
    }

    private function getLoadMoreButton(Paginator $paginator, string $message): HtmlString
    {
        return new HtmlString(Blade::render(<<<'BLADE'
                @if ($perPage < 100 && $paginator->hasMorePages())
                    <div x-intersect="$wire.loadMore()"></div>
                @elseif ($perPage > 10)
                    <div class="text-center text-slate-400">{{ $message }}</div>
                @endif
            BLADE, ['perPage' => $this->perPage, 'paginator' => $paginator, 'message' => $message]));
    }
}
