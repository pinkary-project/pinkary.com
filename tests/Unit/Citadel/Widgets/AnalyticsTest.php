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

test('displays the percentage', function () {
    PanAnalytic::factory()->create(['impressions' => 100, 'hovers' => 50, 'clicks' => 25]);

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->assertSee('50 (50.0%)')
        ->assertSee('25 (25.0%)');
});

test('sorts by impressions by default', function () {
    PanAnalytic::factory()->create(['impressions' => 100]);
    PanAnalytic::factory()->create(['impressions' => 200]);
    PanAnalytic::factory()->create(['impressions' => 300]);

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->assertCanSeeTableRecords(PanAnalytic::orderBy('impressions', 'desc')->get(), inOrder: true);
});

test('searches by name', function () {
    $analytics = PanAnalytic::factory(3)->create();

    $name = $analytics->first()->name;

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->searchTable($name)
        ->assertCountTableRecords(1)
        ->assertCanSeeTableRecords($analytics->where('name', $name))
        ->assertCanNotSeeTableRecords($analytics->where('name', '!=', $name));
});

test('sorts by name', function () {
    $analytics = PanAnalytic::factory(3)->create();

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->sortTable('name')
        ->assertCanSeeTableRecords($analytics->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($analytics->sortByDesc('name'), inOrder: true);
});
