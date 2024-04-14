<?php

declare(strict_types=1);

arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toHaveMethod('render')
    ->toUse('Illuminate\View\View')
    ->toOnlyBeUsedIn([
        'App\Http\Controllers',
        'App\Http\Livewire',
    ]);
