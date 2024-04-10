<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class IncrementViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The models to check if viewed and increment.
     *
     * @phpstan-ignore-next-line
     */
    protected EloquentCollection $models;

    /**
     * The user or session id.
     */
    protected int|string $id;

    /**
     * The models that should be incremented.
     *
     * @var Collection<int, Model>
     */
    protected Collection $modelsToIncrement;

    /**
     * Create a new job instance.
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(EloquentCollection $models, int|string $id)
    {
        $this->models = $models;
        $this->id = $id;
        $this->modelsToIncrement = collect();
    }

    /**
     * Static factory method to create a new job instance.
     *
     * @phpstan-ignore-next-line
     */
    public static function of(EloquentCollection|Model $models): self
    {
        $id = auth()->id() ?? request()->session()->getId();
        $models = $models instanceof Model ? new EloquentCollection([$models]) : $models;

        return new self($models, $id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->models->isEmpty()) {
            return;
        }

        $key = "viewed.{$this->getModelName()}.for.user.{$this->id}";

        $lock = Cache::lock($key);
        try {
            $lock->block(5);
            $viewedItems = Cache::get($key, []);

            $this->models->each(function (Model $model) use ($viewedItems) {
                /* @phpstan-ignore-next-line */
                if (! in_array($model->id, $viewedItems, true)) {
                    $this->modelsToIncrement->push($model);
                }
            });

            Cache::put(
                $key,
                array_unique(array_merge(
                    $viewedItems,
                    $this->modelsToIncrement->pluck('id')->toArray()
                )),
                now()->addMinutes(120)
            );

        } catch (LockTimeoutException $e) {
            $this->release(10);
            logger()->error('LockTimeoutException: '.$e->getMessage());
        } finally {
            $lock->release();
        }

        DB::transaction(function () {
            $this->modelsToIncrement->each(fn (Model $model) => $model
                ->withoutEvents(fn () => $model->increment('views')
                )
            );
        });
    }

    /**
     * Lowercase name of the model.
     */
    public function getModelName(): string
    {
        if ($this->models->isEmpty()) {
            return '';
        }

        /* @phpstan-ignore-next-line */
        return mb_strtolower(class_basename($this->models->first()));
    }
}
