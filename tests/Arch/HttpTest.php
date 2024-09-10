<?php

declare(strict_types=1);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtendNothing()
    ->not->toBeUsed();

arch('middleware')
    ->expect('App\Http\Middleware')
    ->toHaveMethod('handle')
    ->toUse('Illuminate\Http\Request')
    ->not->toBeUsed();

arch('requests')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toHaveMethod('rules')
    ->toBeUsedIn('App\Http\Controllers');

arch('api controllers')
    ->expect('App\Http\Controllers\Api')
    ->toExtendNothing();

arch('api resources')
    ->expect('App\Http\Resources')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');
