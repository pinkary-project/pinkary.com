<?php

declare(strict_types=1);

use App\Livewire\Updates\Create;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('render', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class);

    $component->assertStatus(200)->assertSee('Share an update...');
});

test('store', function () {
    $user = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('answer', 'Hello World');

    $component->call('store');
    $component->assertSet('answer', '');

    $component->assertDispatched('notification.created', 'Update shared.');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($user->id)
        ->and($question->to_id)->toBe($user->id)
        ->and($question->content)->toBeNull()
        ->and($question->answer)->toBe('Hello World')
        ->and($question->anonymously)->toBeFalse();
});
