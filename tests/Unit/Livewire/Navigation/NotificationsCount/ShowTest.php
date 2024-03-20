<?php

declare(strict_types=1);

use App\Livewire\Navigation\NotificationsCount\Show;
use App\Models\Question;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays no notifications by default', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Show::class);

    $component->assertDontSeeHtml([<<<'HTML'
                <span class="bg-purple-600 ml-1 rounded-full px-2 py-1 text-xs font-semibold text-white">
                    0
                </span>
        HTML,
    ]);
});

test('displays the number of notifications', function () {
    $question = Question::factory()->create([
        'answer' => null,
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($question->to)->test(Show::class);

    $component->assertSeeHtml([<<<'HTML'
                <span class="bg-purple-600 ml-1 rounded-full px-2 py-1 text-xs font-semibold text-white">
                    1
                </span>
        HTML,
    ]);
});

test('displays 20+ notifications when there are more than 20', function () {
    $user = User::factory()->create();

    Question::factory(21)->create([
        'to_id' => $user->id,
        'answer' => null,
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Show::class);

    $component->assertSeeHtml([<<<'HTML'
                <span class="bg-purple-600 ml-1 rounded-full px-2 py-1 text-xs font-semibold text-white">
                    20+
                </span>
        HTML,
    ]);
});

test('is refreshable', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Show::class);

    $component->call('refresh');

    $this->expectNotToPerformAssertions();
});
