<?php

declare(strict_types=1);

use App\Livewire\Notifications\Index;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays notifications', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $questionA = Question::factory()->create([
        'to_id' => $userA->id,
        'from_id' => $userB->id,
        'content' => 'Question content 1',
    ]);

    $questionB = Question::factory()->create([
        'to_id' => $userA->id,
        'from_id' => $userB->id,
        'content' => 'Question content 2',
    ]);

    $questionC = Question::factory()->create([
        'to_id' => $userB->id,
        'from_id' => $userA->id,
        'content' => 'Question content 3',
        'answer' => 'Answer content 3',
    ]);

    $userA->notify(new QuestionAnswered($questionC));

    /** @var Testable $component */
    $component = Livewire::actingAs($userA->fresh())->test(Index::class);

    $component
        ->assertSee([
            'Question content 1',
            'Question content 2',
            'Question content 3',
        ]);

    $component->assertSee('Ignore all');
});

test('ignores all notifications', function () {
    $user = User::factory()->create();

    Question::factory(2)->create([
        'to_id' => $user->id,
    ]);

    expect($user->notifications()->count())->toBe(2);

    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $component->call('ignoreAll', now()->toDateTimeString());

    $component->assertDispatched('question.ignored');
    $component->assertDispatched('notification.created', message: 'Notifications ignored.');

    expect($user->notifications()->count())->toBe(0);
    expect($user->questionsReceived()->where('is_ignored', true)->count())->toBe(2);

    $component->assertDontSee('Ignore all');
});

test('ignores all notifications viewed by user', function () {
    $user = User::factory()->create();

    Question::factory(2)->create([
        'to_id' => $user->id,
    ]);

    expect($user->notifications()->count())->toBe(2);

    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $untilDatetime = now()->toDateTimeString();

    $this->travel(10)->seconds();

    Question::factory(2)->create([
        'to_id' => $user->id,
    ]);

    expect($user->notifications()->count())->toBe(4);

    $component->call('ignoreAll', $untilDatetime);

    $component->assertDispatched('question.ignored');
    $component->assertDispatched('notification.created', message: 'Notifications ignored.');

    expect($user->notifications()->count())->toBe(2);
    expect($user->questionsReceived()->where('is_ignored', true)->count())->toBe(2);

    $component->assertSee('Ignore all');
});
