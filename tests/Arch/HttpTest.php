<?php

declare(strict_types=1);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtendNothing()
    ->not->toBeUsed();

arch('middleware')
    ->expect('App\Http\Middleware')
    ->classes()
    ->toHaveMethod('handle')
    ->toUse('Illuminate\Http\Request')
    ->not->toBeUsed();

arch('requests')
    ->expect('App\Http\Requests')
    ->classes()
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toHaveMethod('rules')
    ->toBeUsedIn('App\Http\Controllers');
