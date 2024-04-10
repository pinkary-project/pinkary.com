<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;

it('increments models when not viewed before', function () {
    $models = Question::factory()->count(3)->create();
    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id);

    $job->handle();

    $models->each(fn ($model) => expect($model->views)->toBe(1));
});

it('caches viewed items', function () {
    $models = Question::factory()->count(3)->create();
    $user = User::factory()->create();

    /* @phpstan-ignore-next-line */
    $modelName = mb_strtolower(class_basename($models->first()));

    $job = new IncrementViews($models, $user->id);

    $job->handle();

    $models->each(fn ($model) => expect($model->views)->toBe(1));
    expect(Cache::get("viewed.{$modelName}.for.user.{$user->id}"))->toBe($models->pluck('id')->toArray());
});

it('does not increment models when already viewed', function () {
    $models = Question::factory()->count(3)->create();
    $user = User::factory()->create();
    /* @phpstan-ignore-next-line */
    $modelName = mb_strtolower(class_basename($models->first()));
    Cache::put("viewed.{$modelName}.for.user.{$user->id}", $models->pluck('id')->toArray(), now()->addMinutes(10));
    $job = new IncrementViews($models, $user->id);

    $job->handle();

    $models->each(fn ($model) => expect($model->views)->toBe(0));
});

it('releases lock when exception occurs', function () {
    $models = Question::factory()->count(3)->create();
    Cache::shouldReceive('lock')->andThrow(new LockTimeoutException);
    /* @phpstan-ignore-next-line */
    $modelName = mb_strtolower(class_basename($models->first()));

    $job = new IncrementViews($models, 1);
    $job->handle();

    expect(Cache::lock("viewed.{$modelName}.for.user.1")->get())->toBeTrue();
})->throws(LockTimeoutException::class);

it('caches using session id when no user', function () {
    $models = Question::factory()->count(3)->create();
    Session::shouldReceive('getId')->andReturn('session-id');
    $sessionId = Session::getId();
    $job = new IncrementViews($models, $sessionId);
    $job->handle();

    $modelName = mb_strtolower(class_basename($models->first()));

    $models->each(fn ($model) => expect($model->views)->toBe(1));
    expect(Cache::get("viewed.{$modelName}.for.user.{$sessionId}"))
        ->toBe($models->pluck('id')->toArray());
});

it('increments the given column', function () {
    Schema::table('questions', function ($table) {
        $table->integer('test_column')->default(0);
    });

    $model = Question::factory()->create();
    /* @phpstan-ignore-next-line */
    $model->test_column = 0;

    $models = $model->newCollection([$model]);
    $user = User::factory()->create();

    $job = new IncrementViews($models, $user->id, 'test_column');

    $job->handle();

    /* @phpstan-ignore-next-line */
    $models->each(fn ($model) => expect($model->test_column)->toBe(1));
});
