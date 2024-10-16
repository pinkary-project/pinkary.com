<?php

declare(strict_types=1);

use App\Filament\Widgets\Analytics;
use App\Models\PanAnalytic;
use Livewire\Livewire;

test('displays the analytics', function () {
    PanAnalytic::factory(3)->create();

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->assertCountTableRecords(3);
});

test('displays if impressions is zero', function () {
    PanAnalytic::factory()->create(['hovers' => 0]);
    PanAnalytic::factory()->create(['clicks' => 0]);
    PanAnalytic::factory()->create(['impressions' => 0]);

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->assertCountTableRecords(3);
});
