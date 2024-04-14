<?php

declare(strict_types=1);

arch('contracts')
    ->expect('App\Contracts')
    ->toBeInterfaces()
    ->toOnlyBeUsedIn([
        'App\Services',
    ]);
