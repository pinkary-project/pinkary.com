<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Widgets\QuestionOverview;
use App\Models\User;

it('can render page', function () {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));
    $this->get(QuestionResource::getUrl('index'))->assertSuccessful();
});

it('has a stats widget', function () {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));

    $response = $this->get(QuestionResource::getUrl('index'));

    $response->assertSeeLivewire(QuestionOverview::class);
});
