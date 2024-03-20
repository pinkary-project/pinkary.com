<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('render', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->assertStatus(200)->assertSee('Ask a question...');
});

test('refreshes when link settings changes', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertSeeHtml('text-blue-500');

    $user->update([
        'settings' => [
            'link_shape' => 'rounded-lg',
            'gradient' => 'from-red-500 to-purple-600',
        ],
    ]);

    $component->dispatch('link-settings.updated');

    $component->assertSeeHtml('text-red-500');
});

test('store', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymous', false);

    $component->assertDispatched('notification.created', 'Question sent.');
    $component->assertDispatched('question.created');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('store auth', function () {
    $user = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    $component = Livewire::test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');

    $component->assertRedirect('login');

    expect(App\Models\Question::count())->toBe(0);
});

test('store rate limit', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);
});

test('max 30 questions per day', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    for ($i = 0; $i <= 30; $i++) {
        $component->set('content', 'Hello World');
        $component->call('store');
        $this->travelTo(now()->addMinutes($i));
        $component->assertHasNoErrors();
    }

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 30 questions per day.',
    ]);
});
