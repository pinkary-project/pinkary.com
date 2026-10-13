<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Services\Firewall;
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
    public function __construct(private Collection $viewables, private int|string $id)
    {
        //
    }

    /**
     * Dispatch the job using the authenticated user or session id.
     *
     * @param  Collection<array-key, Question>|Collection<array-key, User>|Question|User  $viewables
     */
    public static function dispatchUsingSession(Collection|Question|User $viewables): ?PendingDispatch
    {
        if (app(Firewall::class)->isBot(request())) {
            return null;
        }

        $id = auth()->id() ?? session()->getId();

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

        /** @var array<int, int> $ids */
        $ids = $this->viewables
            ->filter(fn (Question|User $model): bool => Cache::add("viewed:{$this->getModelName()}:{$model->id}:{$this->id}", true, ttl: now()->addMinutes(120)))
            ->values()
            ->pluck('id')
            ->toArray();

        if ($ids === []) {
            return;
        }

        $model = $this->viewables->first();

        $model::incrementViews($ids);
    }

    /**
     * Lowercase name of the model.
     */
    public function getModelName(): string
    {
        if ($this->viewables->isEmpty()) {
            return '';
        }

        $model = $this->viewables->first();

        return mb_strtolower(class_basename($model));
    }
}
