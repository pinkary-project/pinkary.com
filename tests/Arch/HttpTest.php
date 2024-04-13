<?php

declare(strict_types=1);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Auth\Requests');

arch('middleware')
    ->expect('App\Http\Middleware')
    ->toHaveMethod('handle');
