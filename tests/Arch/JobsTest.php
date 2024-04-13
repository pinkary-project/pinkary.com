<?php

declare(strict_types=1);

arch('jobs')
    ->expect('App\Jobs')
    ->toHaveMethod('handle')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');
