<?php

declare(strict_types=1);

arch('commands')
    ->expect('App\Console\Commands')
    ->toHaveAttribute('Symfony\Component\Console\Attribute\AsCommand')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveSuffix('Command')
    ->toHaveMethod('handle')
    ->toImplementNothing()
    ->toBeFinal();
