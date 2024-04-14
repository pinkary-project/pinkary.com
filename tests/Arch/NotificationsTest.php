<?php

declare(strict_types=1);

arch('notifications')
    ->expect('App\Notifications')
    ->classes()
    ->toHaveConstructor()
    ->toExtend('Illuminate\Notifications\Notification')
    ->toUse('Illuminate\Bus\Queueable')
    ->toOnlyBeUsedIn([
        'App\Console\Commands',
        'App\Http\Controllers',
        'App\Observers',
    ]);
