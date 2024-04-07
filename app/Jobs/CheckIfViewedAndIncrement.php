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

final class CheckIfViewedAndIncrement implements ShouldQueue
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
    public function __construct(EloquentCollection $models, int|string $id, string $column = 'views')
    {
        $this->models = $models;
        $this->id = $id;
        $this->column = $column;
        $this->modelsToIncrement = collect();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $key = "viewed.items.for.user.{$this->id}";

        $lock = Cache::lock($key);
        try {
            $lock->block(5);
            $viewedItems = Cache::get($key, []);

            $this->models->each(function (Model $model) use ($viewedItems) {
                // if the model id is not in the viewed items
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
            logger('cache lock timeout in CheckIfViewedAndIncrement job');
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
