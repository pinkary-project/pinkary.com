<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Collection;

it('increments models when not viewed before', function () {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);
    $job->handle();

    $models->fresh()->each(fn ($model) => expect($model->views)->toBe(1));
});

it('caches viewed items', function () {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);
    $job->handle();

    $models->fresh()->each(fn ($model) => expect($model->views)->toBe(1));
    expect(Cache::get("viewed.{$job->getModelName()}.for.user.{$user->id}"))
        ->toBe($models->pluck('id')->toArray());
});

it('does not increment models when already viewed', function () {
    $models = Question::factory()->count(3)->create(['views' => 1]);

    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);

    Cache::put("viewed.{$job->getModelName()}.for.user.{$user->id}", $models->pluck('id')->toArray(), now()->addMinutes(10));

    $job->handle();

    $models->each(fn ($model) => expect($model->views)->toBe(1));
});

it('releases lock when exception occurs', function () {
    $models = Question::factory()->count(3)->create();

    Cache::shouldReceive('lock')->andThrow(new LockTimeoutException);

    $job = new IncrementViews($models, 1);
    $job->handle();

    expect(Cache::lock("viewed.{$job->getModelName()}.for.user.1")->get())->toBeTrue();
})->throws(LockTimeoutException::class);

it('caches using session id when no user', function () {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    Session::shouldReceive('getId')->andReturn('session-id');

    $sessionId = Session::getId();

    $job = new IncrementViews($models, $sessionId);
    $job->handle();

    $models->fresh()->each(fn ($model) => expect($model->views)->toBe(1));

    expect(Cache::get("viewed.{$job->getModelName()}.for.user.{$sessionId}"))
        ->toBe($models->pluck('id')->toArray());
});

it('does not increment models when is a bot', function () {
    $models = Question::factory()->count(3)->create(['views' => 0]);

    request()->server->set('HTTP_USER_AGENT', 'Storebot-Google');
    request()->headers->set('User-Agent', 'Storebot-Google');

    expect(IncrementViews::dispatchUsingSession($models))->toBeNull();
});

it('handles empty models', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $model = new Collection();
    $pendingDispatch = IncrementViews::dispatchUsingSession($model);

    $job = (fn () => $pendingDispatch->job)->call($pendingDispatch);

    $key = "viewed.{$job->getModelName()}.for.user.{$user->id}";
    expect(Cache::has($key))->toBeFalse();
});
