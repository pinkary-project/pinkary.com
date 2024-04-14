<?php

declare(strict_types=1);

arch('services')
    ->expect('App\Services')
    ->classes()
    ->toBeReadonly()
    ->toExtendNothing();

arch('parsable content services')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider')
    ->toHaveMethod('parse')
    ->toOnlyBeUsedIn([
        'App\Services',
    ]);
