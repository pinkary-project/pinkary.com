<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Livewire\Livewire;

it('can be listed', function () {
    $questions = Question::factory()->count(10)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions);
});

it('can filtered by reported', function () {
    $questions = Question::factory()->count(10)->create([
        'is_reported' => rand(0, 1),
    ]);

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions)
        ->filterTable('is_reported')
        ->assertCanSeeTableRecords($questions->where('is_reported', true))
        ->assertCanNotSeeTableRecords($questions->where('is_reported', false));
});
