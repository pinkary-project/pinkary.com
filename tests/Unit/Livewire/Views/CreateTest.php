<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Views\Create;
use App\Models\Question;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('component can be rendered', function (): void {
    Livewire::test(Create::class)->assertStatus(200);
});

test('updateViews dispatches the job with the correct data', function (): void {
    Queue::fake();

    $component = Livewire::test(Create::class);

    $postIds = [1, 2, 3];

    $questions = collect($postIds)->map(fn (string $postId): Question => new Question()->setRawAttributes(['id' => $postId]));

    $component->call('store', $postIds);

    Queue::assertPushed(fn (IncrementViews $job): bool => invade($job)->viewables->pluck('id')->toArray() === $questions->pluck('id')->toArray());
});
