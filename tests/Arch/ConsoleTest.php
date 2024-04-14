<?php

declare(strict_types=1);

arch('commands')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveSuffix('Command')
    ->toHaveMethod('handle')
    ->toImplementNothing()
    ->not->toBeUsed();
