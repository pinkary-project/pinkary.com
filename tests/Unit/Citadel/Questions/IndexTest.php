<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Livewire\Livewire;

it('can list Question', function () {
    $questions = Question::factory()->count(10)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions);
});

it('can filter questions by `is_reported`', function () {
    $questions = Question::factory()->count(10)->create([
        'is_reported' => rand(0, 1),
    ]);

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions)
        ->filterTable('is_reported')
        ->assertCanSeeTableRecords($questions->where('is_reported', true))
        ->assertCanNotSeeTableRecords($questions->where('is_reported', false));
});
