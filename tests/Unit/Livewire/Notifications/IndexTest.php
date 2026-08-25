<?php

declare(strict_types=1);

use App\Jobs\DeleteOrphanNotifications;
use App\Livewire\Notifications\Index;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('displays notifications', function (): void {
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

test('renders notifications without per-notification question queries', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Question::factory(3)->create([
        'to_id' => $user->id,
        'from_id' => $other->id,
        'content' => 'Question content',
    ]);

    expect($user->notifications()->count())->toBe(3);

    DB::enableQueryLog();

    /** @var Testable $component */
    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $questionQueries = collect(DB::getQueryLog())
        ->filter(fn (array $entry): bool => str_contains($entry['query'], '`questions`'))
        ->count();

    DB::disableQueryLog();

    expect($questionQueries)->toBe(1)
        ->and($component->viewData('questions')->count())->toBe(3);
});

test('notifications referencing deleted questions are skipped, not deleted', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Question::factory()->create([
        'to_id' => $user->id,
        'from_id' => $other->id,
    ]);

    DatabaseNotification::query()->create([
        'id' => Str::uuid()->toString(),
        'type' => QuestionCreated::class,
        'notifiable_type' => $user::class,
        'notifiable_id' => $user->getKey(),
        'data' => ['question_id' => Str::uuid()->toString()],
    ]);

    expect($user->notifications()->count())->toBe(2);

    /** @var Testable $component */
    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    expect($component->viewData('questions')->count())->toBe(1)
        ->and($user->notifications()->count())->toBe(2);
});

test('orphan notifications are deleted by the cleanup job', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $keptQuestion = Question::factory()->create([
        'to_id' => $user->id,
        'from_id' => $other->id,
    ]);

    DatabaseNotification::query()->create([
        'id' => Str::uuid()->toString(),
        'type' => QuestionCreated::class,
        'notifiable_type' => $user::class,
        'notifiable_id' => $user->getKey(),
        'data' => ['question_id' => Str::uuid()->toString()],
    ]);

    expect($user->notifications()->count())->toBe(2);

    new DeleteOrphanNotifications()->handle();

    expect($user->notifications()->count())->toBe(1)
        ->and($user->notifications()->first()->data['question_id'])->toBe($keptQuestion->id);
});

test('ignores all notifications', function (): void {
    $user = User::factory()->create();

    Question::factory(2)->create([
        'to_id' => $user->id,
    ]);

    expect($user->notifications()->count())->toBe(2);

    $component = Livewire::actingAs($user->fresh())->test(Index::class);

    $component->call('ignoreAll', now()->toDateTimeString());

    $component->assertDispatched('question.ignored');
    $component->assertDispatched('notification.created', message: 'Notifications ignored.');

    expect($user->notifications()->count())->toBe(0)
        ->and($user->questionsReceived()->where('is_ignored', true)->count())->toBe(2);

    $component->assertDontSee('Ignore all');
});

test('ignores all notifications viewed by user', function (): void {
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

    expect($user->notifications()->count())->toBe(2)
        ->and($user->questionsReceived()->where('is_ignored', true)->count())->toBe(2);

    $component->assertSee('Ignore all');
});
