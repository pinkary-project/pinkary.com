<?php

declare(strict_types=1);

arch('rules')
    ->expect('App\Rules')
    ->toExtendNothing()
    ->toImplement('Illuminate\Contracts\Validation\ValidationRule')
    ->toOnlyBeUsedIn([
        'App\Http\Controllers',
        'App\Http\Requests',
        'App\Livewire',
    ]);
