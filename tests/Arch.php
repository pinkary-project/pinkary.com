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
    ->toHaveMethod('handle')
    ->toBeFinal();

arch('contracts')
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Auth')
    ->classes()
    ->toBeFinal();

arch('middleware')
    ->expect('App\Http\Middleware')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('handle');

arch('jobs')
    ->expect('App\Jobs')
    ->toHaveConstructor()
    ->toHaveMethod('handle')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->classes()
    ->toBeFinal();

arch('mailables')
    ->expect('App\Mail')
    ->toHaveConstructor()
    ->toExtend('Illuminate\Mail\Mailable');

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toBeClasses()
    ->toBeFinal();

arch('notifications')
    ->expect('App\Notifications')
    ->toHaveConstructor()
    ->toExtend('Illuminate\Notifications\Notification');

arch('observers')
    ->expect('App\Observers')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('policies')
    ->expect('App\Policies')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('providers')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider')
    ->toBeFinal();

arch('rules')
    ->expect('App\Rules')
    ->toImplement('Illuminate\Contracts\Validation\ValidationRule')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('services')
    ->expect('App\Services')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('parsable content providers')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('view components')
    ->expect('App\View\Components')
    ->toExtend('Illuminate\View\Component')
    ->toHaveMethod('render')
    ->toBeFinal();

arch('avoid extends')
    ->expect('App')
    ->classes()
    ->toBeFinal();

arch('avoid mutation')
    ->expect('App')
    ->classes()
    //->toBeReadonlyUnlessExtends()
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
