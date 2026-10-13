<?php

declare(strict_types=1);

arch('notifications')
    ->expect('App\Notifications')
    ->toHaveConstructor()
    ->toExtend(Illuminate\Notifications\Notification::class)
    ->toOnlyBeUsedIn([
        'App\Actions\Notifications',
        'App\Actions\Users',
        'App\Console\Commands',
        'App\Http\Controllers',
        'App\Observers',
        'App\Livewire\Concerns',
        App\Livewire\Notifications\Index::class,
    ]);
