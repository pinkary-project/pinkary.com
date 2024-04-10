<?php

declare(strict_types=1);

arch('Expect application to be using strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('Expect the following functions to not be used')
    ->expect(['dd', 'dump', 'die', 'var_dump'])
    ->not->toBeUsed();

arch('Expect the following helpers only to be used in specific namespaces')
    ->expect(['session', 'auth', 'request'])
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Livewire',
    ]);

arch('Expect all console commands to extend Command')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveMethod('handle')
    ->toBeFinal();

arch('Expect all contracts to be interfaces')
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch('Expect all controllers to be final')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Auth')
    ->classes()
    ->toBeFinal();

arch('Expect all middeleware to have handle method')
    ->expect('App\Http\Middleware')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('handle');

arch('Expect all jobs to implement ShouldQueue')
    ->expect('App\Jobs')
    ->toHaveConstructor()
    ->toHaveMethod('handle')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('Expect all livewire components to extend Component')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->classes()
    ->toBeFinal();

arch('Expect all mail classes to extend Mailable')
    ->expect('App\Mail')
    ->toHaveConstructor()
    // ->toHaveTraits([
    //     'Illuminate\Bus\Queueable',
    //     'Illuminate\Queue\SerializesModels',
    // ])
    ->toExtend('Illuminate\Mail\Mailable');

arch('Expect all models to be classes')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    //->toHaveTrait('Illuminate\Database\Eloquent\Factories\HasFactory')
    ->toBeClasses()
    ->toBeFinal();

arch('Expect all notifications to extend Notification')
    ->expect('App\Notifications')
    ->toHaveConstructor()
    // ->toHaveTrait('Illuminate\Bus\Queueable)
    ->toExtend('Illuminate\Notifications\Notification');

arch('Expect all observers to be readonly classes')
    ->expect('App\Observers')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('Expect all policies to be readonly classes')
    ->expect('App\Policies')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('Expect all providers to extend ServiceProvider')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider')
    ->toBeFinal();

arch('Expect all rules to implement ValidationRule')
    ->expect('App\Rules')
    ->toImplement('Illuminate\Contracts\Validation\ValidationRule')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('Expect all service classes to implement something')
    ->expect('App\Services')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('Expect all parsable content providers to implement ParsableContentProvider')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider')
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing();

arch('Expect all view component classes to extend Component')
    ->expect('App\View\Components')
    ->toExtend('Illuminate\View\Component')
    ->toHaveMethod('render')
    ->toBeFinal();
