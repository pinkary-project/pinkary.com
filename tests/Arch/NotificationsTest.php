<?php

declare(strict_types=1);

arch('notifications')
    ->expect('App\Notifications')
    ->toHaveConstructor()
    ->toExtend(Illuminate\Notifications\Notification::class)
    ->toOnlyBeUsedIn([
        'App\Console\Commands',
        'App\Http\Controllers',
        'App\Observers',
        App\Livewire\Notifications\Index::class,
    ]);
