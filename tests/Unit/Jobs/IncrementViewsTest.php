<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

it('increments models when not viewed before', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);
    $job->handle();

    $models->fresh()->each(function ($model): void {
        expect($model->views)->toBe(1);
    });
});

it('caches viewed items', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);
    $job->handle();

    $models->fresh()->each(function ($model): void {
        expect($model->views)->toBe(1);
    });

    $models->each(function (Question $model) use ($user): void {
        expect(Cache::has("viewed:question:{$model->id}:{$user->id}"))->toBeTrue();
    });
});

it('does not increment models when already viewed', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 1]);

    $user = User::factory()->create();

    $models->each(function (Question $model) use ($user): void {
        Cache::put("viewed:question:{$model->id}:{$user->id}", true, now()->addMinutes(10));
    });

    $job = new IncrementViews($models, $user->id);
    $job->handle();

    $models->each(function ($model): void {
        expect($model->views)->toBe(1);
    });
});

it('does not double-count views when dispatched twice', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    $user = User::factory()->create();

    new IncrementViews($models, $user->id)->handle();
    new IncrementViews($models, $user->id)->handle();

    $models->fresh()->each(function ($model): void {
        expect($model->views)->toBe(1);
    });

    $models->each(function (Question $model) use ($user): void {
        expect(Cache::has("viewed:question:{$model->id}:{$user->id}"))->toBeTrue();
    });
});

it('caches using session id when no user', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    Session::shouldReceive('getId')->andReturn('session-id');

    $sessionId = Session::getId();

    $job = new IncrementViews($models, $sessionId);
    $job->handle();

    $models->fresh()->each(function ($model): void {
        expect($model->views)->toBe(1);
    });

    $models->each(function (Question $model) use ($sessionId): void {
        expect(Cache::has("viewed:question:{$model->id}:{$sessionId}"))->toBeTrue();
    });
});

it('does not increment models when is a bot', function (): void {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    request()->server->set('HTTP_USER_AGENT', 'Storebot-Google');
    request()->headers->set('User-Agent', 'Storebot-Google');

    expect(IncrementViews::dispatchUsingSession($models))->toBeNull();
});

it('handles empty models', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $model = new Collection();
    $pendingDispatch = IncrementViews::dispatchUsingSession($model);

    $job = (fn () => $pendingDispatch->job)->call($pendingDispatch);

    $job->handle();

    expect(Cache::has("viewed:user:{$user->id}:{$user->id}"))->toBeFalse();
});

it('returns an empty model name when there are no viewables', function (): void {
    $job = new IncrementViews(new Collection(), 'session-id');

    expect($job->getModelName())->toBeEmpty();
});
