<?php

declare(strict_types=1);
use App\Filament\Widgets\Analytics;
use Livewire\Livewire;

test('displays the analytics', function () {
    Livewire::test(Analytics::class)
        ->assertSee('Analytics');
});
