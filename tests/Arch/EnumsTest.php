<?php

declare(strict_types=1);

arch('enums')
    ->expect('App\Enums')
    ->enums()
    ->toBeEnums()
    ->toExtendNothing()
    ->toUseNothing()
    ->toHaveMethod('toArray')
    ->not->toHaveConstructor()
    ->toOnlyBeUsedIn([
        'App\Console\Commands',
        'App\Http\Requests',
        'App\Livewire',
        'App\Models',
    ]);
