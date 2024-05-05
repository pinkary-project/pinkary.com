<?php

declare(strict_types=1);

arch('globals')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'sleep'])
    ->not->toBeUsed();

arch('http helpers')
    ->expect(['session', 'auth', 'request'])
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Livewire',
        'App\Jobs\IncrementViews',
    ]);
