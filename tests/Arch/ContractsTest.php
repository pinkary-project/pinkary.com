<?php

declare(strict_types=1);

arch('contracts')
    ->expect('App\Contracts')
    ->interfaces()
    //->toHaveAtLeastOneMethod()
    ->toBeInterfaces();

// arch('implement methods')
//     ->expect('App')
//     ->classes()
//     ->toHaveImplementedMethods();

/**
 * New Expectations:
 * toHaveAtLeastOneMethod
 * toHaveImplementedMethods
 */
