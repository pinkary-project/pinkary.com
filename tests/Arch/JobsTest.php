<?php

declare(strict_types=1);

arch('jobs')
    ->expect('App\Jobs')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('handle')
    ->toHaveConstructor()
    ->toExtendNothing()
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->toUse([
        'Illuminate\Bus\Queueable',
        'Illuminate\Foundation\Bus\Dispatchable',
        'Illuminate\Queue\InteractsWithQueue',
        'Illuminate\Queue\SerializesModels',
        'Illuminate\Support\Facades\Storage',
    ]);
