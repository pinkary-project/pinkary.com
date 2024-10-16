<?php

declare(strict_types=1);

use App\Filament\Widgets\Analytics;
use Livewire\Livewire;
use Pan\Contracts\AnalyticsRepository;
use Pan\Enums\EventType;

test('displays the analytics', function () {

    $analyticsRepository = app(AnalyticsRepository::class);

    $analyticsRepository->increment('following_tab', EventType::IMPRESSION);
    $analyticsRepository->increment('recent_tab', EventType::CLICK);
    $analyticsRepository->increment('trading_tab', EventType::HOVER);

    Livewire::test(Analytics::class)
        ->assertSee('Analytics')
        ->assertCountTableRecords(3);
});
