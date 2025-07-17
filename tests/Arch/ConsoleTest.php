<?php

declare(strict_types=1);
use Symfony\Component\Console\Command\SignalableCommandInterface;

arch('commands')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveSuffix('Command')
    ->toHaveMethod('handle')
    ->toOnlyImplement(SignalableCommandInterface::class)
    ->not->toBeUsed();
