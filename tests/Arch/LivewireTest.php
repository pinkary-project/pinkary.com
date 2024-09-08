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
        'App\Providers\AppServiceProvider',
    ])
    ->ignoring('App\Livewire\Concerns')
    ->not->toUse(['redirect', 'to_route', 'back']);

arch('livewire concerns')
    ->expect('App\Livewire\Concerns')
    ->toBeTraits();
