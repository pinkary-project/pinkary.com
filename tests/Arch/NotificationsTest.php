<?php

declare(strict_types=1);

arch('notifications')
    ->expect('App\Notifications')
    ->toExtend('Illuminate\Notifications\Notification');
