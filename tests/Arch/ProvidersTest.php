<?php

declare(strict_types=1);

arch('providers')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider')
    ->not->toBeUsed();
