<?php

declare(strict_types=1);

arch('mailables')
    ->expect('App\Mail')
    ->toHaveConstructor()
    ->toExtend('Illuminate\Mail\Mailable');
