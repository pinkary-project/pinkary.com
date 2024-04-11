<?php

declare(strict_types=1);

arch('strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('functions')
    ->expect(['dd', 'dump', 'die', 'var_dump'])
    ->not->toBeUsed();

arch('helpers')
    ->expect(['session', 'auth', 'request'])
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Livewire',
    ]);

arch('commands')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveMethod('handle');

arch('contracts')
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Auth\Requests');

arch('middleware')
    ->expect('App\Http\Middleware')
    ->toHaveMethod('handle');

arch('jobs')
    ->expect('App\Jobs')
    ->toHaveMethod('handle')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toBeFinal();

arch('mailables')
    ->expect('App\Mail')
    ->toExtend('Illuminate\Mail\Mailable');

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toBeFinal();

arch('notifications')
    ->expect('App\Notifications')
    ->toExtend('Illuminate\Notifications\Notification');

arch('providers')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider');

arch('rules')
    ->expect('App\Rules')
    ->toImplement('Illuminate\Contracts\Validation\ValidationRule');

arch('parsable content providers')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider');

arch('view components')
    ->expect('App\View\Components')
    ->toExtend('Illuminate\View\Component')
    ->toHaveMethod('render');

arch('avoid open for extension')
    ->expect('App')
    ->classes()
    ->toBeFinal();

arch('avoid mutation')
    ->expect('App')
    ->classes()
    ->toBeReadonly()
    ->ignoring([
        'App\Console\Commands',
        'App\Exceptions',
        'App\Http\Controllers\Auth\Requests',
        'App\Jobs',
        'App\Livewire',
        'App\Mail',
        'App\Models',
        'App\Notifications',
        'App\Providers',
        'App\View',
    ]);

arch('avoid inheritance')
    ->expect('App')
    ->classes()
    ->toExtendNothing()
    ->ignoring([
        'App\Console\Commands',
        'App\Exceptions',
        'App\Http\Controllers\Auth\Requests',
        'App\Jobs',
        'App\Livewire',
        'App\Mail',
        'App\Models',
        'App\Notifications',
        'App\Providers',
        'App\View',
    ]);

arch('ensure factories', function () {
    foreach (getModels() as $model) {
        expect($model::factory())
            ->toBeInstanceOf(Illuminate\Database\Eloquent\Factories\Factory::class);
    }
});

arch('ensure datetime casts', function () {
    foreach (getModels() as $model) {
        $instance = $model::factory()->create();

        $dates = collect($instance->getAttributes())
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
    return collect(glob(__DIR__.'/../app/Models/*.php'))
        ->map(function ($file) {
            return 'App\Models\\'.basename($file, '.php');
        })->toArray();
}
