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
     * The column to increment.
     */
    protected string $column;

    /**
     * Create a new job instance.
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(EloquentCollection|Model $models, int|string $id, string $column = 'views')
    {
        $this->models = $models instanceof Model ? $models->newCollection([$models]) : $models;
        $this->id = $id;
        $this->column = $column;
        $this->modelsToIncrement = collect();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /* @phpstan-ignore-next-line */
        $modelType = mb_strtolower(class_basename($this->models->first()));

        $key = "viewed.{$modelType}.for.user.{$this->id}";

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
            $this->modelsToIncrement->each(fn (Model $model) => $model->withoutEvents(fn () => $model->increment($this->column))
            );
        });
    }
}
