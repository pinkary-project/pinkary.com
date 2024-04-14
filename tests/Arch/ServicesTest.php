<?php

declare(strict_types=1);

arch('parsable content services')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider')
    ->toOnlyBeUsedIn([
        'App\Services',
    ]);
