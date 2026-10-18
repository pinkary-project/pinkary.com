<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Filament\Navigation\NavigationItem;
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

it('can filter posts awaiting approval', function (): void {
    $pending = Question::factory()->sharedUpdate()->create(['requires_approval' => true]);
    $approved = Question::factory()->sharedUpdate()->create(['requires_approval' => false]);

    Livewire::test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords([$pending, $approved])
        ->filterTable('requires_approval')
        ->assertCanSeeTableRecords([$pending])
        ->assertCanNotSeeTableRecords([$approved]);
});

it('links to unapproved questions with a matching count and filter', function (): void {
    $pending = Question::factory()->sharedUpdate()->count(2)->create(['requires_approval' => true]);
    $approved = Question::factory()->sharedUpdate()->create(['requires_approval' => false]);
    $filterParameters = [
        'filters' => [
            'requires_approval' => [
                'value' => '1',
            ],
        ],
    ];
    $navigationItem = collect(QuestionResource::getNavigationItems())
        ->first(fn (NavigationItem $item): bool => $item->getLabel() === 'Unapproved questions');

    expect($navigationItem)->not->toBeNull()
        ->and($navigationItem->getBadge())->toBe('2')
        ->and($navigationItem->getUrl())->toBe(QuestionResource::getUrl('index', $filterParameters));

    Livewire::withQueryParams($filterParameters)
        ->test(QuestionResource\Pages\Index::class)
        ->assertCanSeeTableRecords($pending)
        ->assertCanNotSeeTableRecords([$approved]);
});

it('allows an admin to approve a pending post', function (): void {
    $admin = User::factory()->create(['email' => 'enunomaduro@gmail.com']);
    $question = Question::factory()->sharedUpdate()->create(['requires_approval' => true]);

    Livewire::actingAs($admin)
        ->test(QuestionResource\Pages\Index::class)
        ->assertTableActionVisible('approve', $question)
        ->callTableAction('approve', $question);

    expect($question->refresh()->requires_approval)->toBeFalse();
});
