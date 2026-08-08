<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('can be listed', function (): void {
    $questions = Question::factory()->count(10)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions);
});

it('can filtered by reported', function (): void {
    $questions = Question::factory()->count(10)->create([
        'is_reported' => random_int(0, 1),
    ]);

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions)
        ->filterTable('is_reported')
        ->assertCanSeeTableRecords($questions->where('is_reported', true))
        ->assertCanNotSeeTableRecords($questions->where('is_reported', false));
});

it('can filtered by ignored', function (): void {
    $questions = Question::factory()->count(10)->create([
        'is_ignored' => random_int(0, 1),
    ]);

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($questions)
        ->filterTable('is_ignored')
        ->assertCanSeeTableRecords($questions->where('is_ignored', true))
        ->assertCanNotSeeTableRecords($questions->where('is_ignored', false));
});

it('can not see name of the questioner if anonymously', function (): void {
    User::factory()->hasQuestionsSent([
        'anonymously' => true,
    ])->create(['name' => 'Ludovic Guénet']);

    Question::factory(5)->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords(Question::all())
        ->assertDontSee('Ludovic Guénet');
});

it('can delete question', function (): void {
    $question = Question::factory()->create();
    $anotherQuestion = Question::factory()->create();

    Livewire::test(QuestionResource\Pages\Index::class)
        ->callTableAction('ignore', $question);

    expect($question->refresh()->is_ignored)->toBeTrue()
        ->and($anotherQuestion->refresh()->is_ignored)->toBeFalse();
});
