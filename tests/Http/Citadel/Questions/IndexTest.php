<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    actingAs(User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]));
});

it('can render page', function () {
    get(QuestionResource::getUrl('index'))->assertSuccessful();
});

it('can list Question', function () {
    $questions = Question::factory()->count(10)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions);
});
