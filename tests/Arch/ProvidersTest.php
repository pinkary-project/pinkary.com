<?php

declare(strict_types=1);

arch('providers')
    ->expect('App\Providers')
    ->classes()
    ->toHaveConstructor()
    ->toExtend('Illuminate\Support\ServiceProvider')
    ->not->toBeUsed();
