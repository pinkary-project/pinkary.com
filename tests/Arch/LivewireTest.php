<?php

declare(strict_types=1);

arch('livewire components')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toBeFinal();
