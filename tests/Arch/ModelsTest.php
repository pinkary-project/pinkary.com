<?php

declare(strict_types=1);

arch('models')
    ->expect('App\Models')
    ->classes()
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->not->toBeInvokable()
    ->toOnlyBeUsedIn([
        'App\Concerns',
        'App\Console',
        'App\Contracts',
        'App\Http',
        'App\Jobs',
        'App\Livewire',
        'App\Observers',
        'App\Mail',
        'App\Models',
        'App\Notifications',
        'App\Policies',
        'App\Providers',
        'App\Rules',
        'App\Services',
        'Database\Factories',
    ]);

arch('ensure factories', function () {
    foreach (getModels() as $model) {
        /* @var \Illuminate\Database\Eloquent\Factories\HasFactory $model */
        expect($model::factory()) // @phpstan-ignore-line
            ->toBeInstanceOf(Illuminate\Database\Eloquent\Factories\Factory::class);
    }
});

arch('ensure datetime casts', function () {
    foreach (getModels() as $model) {
        /* @var \Illuminate\Database\Eloquent\Factories\HasFactory $model */
        $instance = $model::factory()->create(); //@phpstan-ignore-line

        $dates = collect($instance->getAttributes()) //@phpstan-ignore-line
            ->filter(fn ($_, $key) => str_ends_with($key, '_at'));

        foreach ($dates as $key => $value) {
            expect($instance->getCasts())->toHaveKey($key, 'datetime');
        }
    }
});

/**
 * Get all models in the app/Models directory.
 *
 * @return array<int, class-string<\Illuminate\Database\Eloquent\Model>>
 */
function getModels(): array
{
    return collect(glob(__DIR__.'/../app/Models/*.php')) //@phpstan-ignore-line
        ->map(function ($file) {
            return 'App\Models\\'.basename($file, '.php'); //@phpstan-ignore-line
        })->toArray();
}
