<?php

declare(strict_types=1);

arch('globals')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'sleep'])
    ->not->toBeUsed()
    ->ignoring([

    ]);

arch('helpers')
    ->expect(['session', 'auth', 'request'])
    ->toOnlyBeUsedIn([
        'App\Concerns',
        'App\Console',
        'App\Http',
        'App\Livewire',
    ]);
