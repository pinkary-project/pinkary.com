<?php

declare(strict_types=1);

arch('strict types')
    ->expect('App')
    ->toUseStrictTypes();

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
        'App\Http\Requests',
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
        'App\Http\Requests',
        'App\Jobs',
        'App\Livewire',
        'App\Mail',
        'App\Models',
        'App\Notifications',
        'App\Providers',
        'App\View',
    ]);

/**
 * New Expectations:
 * toHavePublicProperties
 * toHavePublicProperty
 * toHaveProtectedProperties
 * toHaveProtectedProperty
 * toHaveProtectedProperties
 * toHavePrivateProperties
 * toHavePrivateProperty
 * toUseTrait
 * toUseTraits
 * toEnsureMethodsHaveReturnType
 *
 * Modify Expectations:
 * toExtend (Would be good to support array of classes to match)
 */
