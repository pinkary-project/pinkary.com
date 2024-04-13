<?php

declare(strict_types=1);

arch('exceptions')
    ->expect('App\Exceptions')
    ->classes()
    ->toBeFinal()
    ->toExtend('Exception')
    ->toImplement('Throwable')
    ->toOnlyBeUsedIn([
        'App\Console\Commands',
        'App\Http\Controllers',
        'App\Livewire',
        'App\Services',
    ]);
