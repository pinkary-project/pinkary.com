<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;

it('can list Question', function () {
    $questions = Question::factory()->count(10)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions);
});
