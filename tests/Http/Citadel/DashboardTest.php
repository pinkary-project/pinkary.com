<?php

declare(strict_types=1);

use App\Filament\Widgets\Analytics;
use App\Models\User;

it('does renders', function () {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]))
        ->get('citadel/')
        ->assertSeeLivewire(Analytics::class)
        ->assertStatus(200);
});
