<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Views\Create;
use App\Models\Question;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('component can be rendered', function () {
    Livewire::test(Create::class)->assertStatus(200);
});

test('updateViews dispatches the job with the correct data', function () {
    Queue::fake();

    $component = Livewire::test(Create::class);

    $postIds = [1, 2, 3];

    $questions = collect($postIds)->map(fn (string $postId): Question => (new Question())->setRawAttributes(['id' => $postId]));

    $component->call('store', $postIds);

    Queue::assertPushed(function (IncrementViews $job) use ($questions) {
        return invade($job)->viewables->pluck('id')->toArray() === $questions->pluck('id')->toArray();
    });
});
