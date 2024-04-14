<?php

declare(strict_types=1);

arch('livewire components')
    ->expect('App\Livewire')
    ->toBeClasses()
    ->ignoring('App\Livewire\Concerns')
    ->toExtend('Livewire\Component')
    ->ignoring('App\Livewire\Concerns')
    ->toHaveMethod('render')
    ->ignoring('App\Livewire\Concerns')
    ->toOnlyBeUsedIn([
        'App\Http\Controllers',
        'App\Http\Livewire',
    ])
    ->ignoring('App\Livewire\Concerns');

arch('livewire concerns')
    ->expect('App\Livewire\Concerns')
    ->toBeTraits();
