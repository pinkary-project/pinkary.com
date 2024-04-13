<?php

declare(strict_types=1);

arch('mailables')
    ->expect('App\Mail')
    ->classes()
    ->toBeFinal()
    ->toHaveConstructor()
    ->toExtend('Illuminate\Mail\Mailable')
    ->toUse([
        'Illuminate\Bus\Queueable',
        'Illuminate\Mail\Mailable',
        'Illuminate\Mail\Mailables\Content',
        'Illuminate\Mail\Mailables\Envelope',
        'Illuminate\Queue\SerializesModels',
    ]);
