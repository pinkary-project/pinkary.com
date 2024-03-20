<?php

declare(strict_types=1);

use App\Livewire\Notifications\Index;
use App\Models\Question;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays notifications', function () {
    $question = Question::factory()->create([
        'answer' => null,
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($question->to)->test(Index::class);

    $component->assertSee($question->content);

    $question->to->notifications()->get()->each->delete();

    $component = Livewire::actingAs($question->to->fresh())->test(Index::class);

    $component->assertDontSee($question->content);
});
