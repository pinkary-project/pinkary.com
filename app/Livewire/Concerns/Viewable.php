<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Contracts\Models;
use App\Services\Firewall;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

trait Viewable
{
    #[Locked]
    public bool $isViewable = false;

    #[Locked]
    public string|int $viewedKey = 0;

    #[Locked]
    public string $viewable = '';

    /**
     * Mount the viewable model.
     */
    public function setViewable(string $viewable, string|int $key): void
    {
        $this->viewable = $viewable;
        $this->viewedKey = $key;

        if ($this->canBeViewed()) {
            $this->isViewable = true;
        }
    }

    /**
     * Increment the views of the given model.
     */
    #[Renderless]
    public function incrementViews(): void
    {
        if (! $this->canBeViewed()) {
            return;
        }

        /** @var Models\Viewable $model */
        $model = $this->viewable;

        $model::incrementViews([$this->viewedKey]);

        $this->isViewable = false;

        Cache::put(
            $this->getViewsCacheKey(),
            true,
            now()->addMinutes(120)
        );
    }

    /**
     * Determine if the model has been viewed by the current user.
     */
    private function hasBeenViewed(): bool
    {
        return Cache::has(
            $this->getViewsCacheKey(),
        );
    }

    /**
     * Get the cache key for the views.
     */
    private function getViewsCacheKey(): string
    {
        $id = auth()->id() ?? session()->getId();

        /** @var Model $model */
        $model = $this->viewable;

        return (new $model())->getTable().'-'.$this->viewedKey.'-viewed-by-'.$id;
    }

    private function canBeViewed(): bool
    {
        return app(Firewall::class)->isBot(request()) === false
            && $this->hasBeenViewed() === false;
    }
}
