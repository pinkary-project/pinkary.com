<?php

declare(strict_types=1);

arch('providers')
    ->expect('App\Providers')
    ->classes()
    ->toBeFinal()
    ->toHaveConstructor()
    ->toExtend('Illuminate\Support\ServiceProvider')
    ->not->toBeUsed();
