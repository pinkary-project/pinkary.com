<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class IncrementViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  Collection<array-key, Question>|Collection<array-key, User>  $viewables
     */
    public function __construct(protected Collection $viewables, protected int|string $id)
    {
        //
    }

    /**
     * Dispatch the job using the authenticated user or session id.
     *
     * @param  Collection<array-key, Question>|Collection<array-key, User>|Question|User  $viewables
     */
    public static function dispatchUsingSession(Collection|Question|User $viewables): PendingDispatch
    {
        $id = auth()->id() ?? request()->session()->getId();

        /** @var Collection<array-key, Question>|Collection<array-key, User> $viewables */
        $viewables = $viewables instanceof Model ? collect([$viewables]) : $viewables;

        return self::dispatch($viewables, $id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->viewables->isEmpty()) {
            return;
        }

        $key = "viewed.{$this->getModelName()}.for.user.{$this->id}";
        $lock = Cache::lock($key);

        $viewed = new Collection;

        try {
            $lock->block(5);

            /** @var array<int, int> $alreadyViewed */
            $alreadyViewed = Cache::get($key, []);

            $this->viewables->each(function (Question|User $model) use ($alreadyViewed, $viewed): void {
                if (! in_array($model->id, $alreadyViewed, true)) {
                    $viewed->push($model);
                }
            });

            Cache::put(
                $key,
                array_unique(array_merge(
                    $alreadyViewed,
                    $viewed->pluck('id')->toArray()
                )),
                now()->addMinutes(120)
            );
        } catch (LockTimeoutException) {
            $this->release(10);
        } finally {
            $lock->release();
        }

        if ($viewed->isNotEmpty()) {
            /** @var Question|User $model */
            $model = $viewed->first();

            /** @var array<int, int> $ids */
            $ids = $viewed->pluck('id')->toArray();

            $model::incrementViews($ids);
        }
    }

    /**
     * Lowercase name of the model.
     */
    public function getModelName(): string
    {
        if ($this->viewables->isEmpty()) {
            return '';
        }

        /** @var Question|User $model */
        $model = $this->viewables->first();

        return mb_strtolower(class_basename($model));
    }
}
