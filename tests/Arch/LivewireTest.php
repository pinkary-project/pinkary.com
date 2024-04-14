<?php

declare(strict_types=1);

arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toHaveMethod('render')
    ->toOnlyBeUsedIn([
        'App\Http\Controllers',
        'App\Http\Livewire',
    ]);
