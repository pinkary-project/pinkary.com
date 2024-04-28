<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\User;

it('can render page', function () {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));
    $this->get(QuestionResource::getUrl('index'))->assertSuccessful();
});
