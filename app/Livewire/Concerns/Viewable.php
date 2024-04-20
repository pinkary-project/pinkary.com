<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

trait Viewable
{
    #[Locked]
    public bool $viewed = false;

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

        if ($this->hasBeenViewed()) {
            $this->viewed = true;
        }
    }

    /**
     * Increment the views of the given model.
     */
    #[Renderless]
    public function incrementViews(): void
    {
        if ($this->hasBeenViewed()) {
            return;
        }

        /** @var Model $model */
        $model = $this->viewable;

        Model::withoutTimestamps(function () use ($model): void {
            $this->viewableScope($model::query())
                ->where('id', $this->viewedKey)
                ->increment('views');
        });

        $this->viewed = true;

        Cache::put(
            $this->getViewsCacheKey(),
            true,
            now()->addMinutes(120)
        );
    }

    /**
     * viewable model scope.
     */
    protected function viewableScope(Builder $query): Builder
    {
        return $query;
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
}
