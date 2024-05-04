<?php

declare(strict_types=1);

use App\Filament\Resources\QuestionResource\Widgets\QuestionOverview;
use App\Models\Question;
use Livewire\Livewire;

it('displays the correct stats', function () {

    Question::factory()->count(51)->create();

    Question::factory()->count(10)->create([
        'is_reported' => true,
    ]);

    Question::factory()->count(5)->create([
        'is_ignored' => true,
    ]);

    Question::factory()->count(5)->create([
        'is_reported' => true,
        'is_ignored' => true,
    ]);

    $component = Livewire::test(QuestionOverview::class);

    $component->assertSee('Total Questions');
    $component->assertSee('71');

    $component->assertSee('Reported Questions');
    $component->assertSee('15');

    $component->assertSee('Ignored Questions');
    $component->assertSee('10');
});
