<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Widgets\QuestionOverview;
use App\Models\Question;
use App\Models\User;

it('can render page', function (): void {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));
    $this->get(QuestionResource::getUrl('index'))->assertSuccessful();
});

it('shows a link to unapproved questions in the sidebar', function (): void {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));
    Question::factory()->sharedUpdate()->create(['requires_approval' => true]);

    $response = $this->get(QuestionResource::getUrl('index'));

    $response->assertSuccessful()
        ->assertSee('Unapproved questions')
        ->assertSee(QuestionResource::getUrl('index', [
            'filters' => [
                'requires_approval' => [
                    'value' => '1',
                ],
            ],
        ]), false);
});

it('has a stats widget', function (): void {
    $this->actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));

    $response = $this->get(QuestionResource::getUrl('index'));

    $response->assertSeeLivewire(QuestionOverview::class);
});
