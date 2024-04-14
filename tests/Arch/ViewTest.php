<?php

declare(strict_types=1);

arch('view components')
    ->expect('App\View\Components')
    ->toExtend('Illuminate\View\Component')
    ->toHaveMethod('render')
    ->not->toBeUsed();
