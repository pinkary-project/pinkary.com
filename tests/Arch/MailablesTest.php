<?php

declare(strict_types=1);

arch('mailables')
    ->expect('App\Mail')
    ->toExtend('Illuminate\Mail\Mailable');
