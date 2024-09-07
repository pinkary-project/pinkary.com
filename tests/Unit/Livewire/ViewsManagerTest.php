<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\ViewsManager;
use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('component can be rendered', function () {
    Livewire::test(ViewsManager::class)->assertStatus(200);
});

test('updateViews dispatches the job with the correct data', function () {
    Queue::fake();

    $component = Livewire::test(ViewsManager::class);

    $postIds = [1, 2, 3];
    $collection = new Collection();
    foreach ($postIds as $postId) {
        $collection->push((new Question())->setRawAttributes(['id' => $postId]));
    }

    $component->call('updateViews', $postIds);

    Queue::assertPushed(function (IncrementViews $job) use ($collection) {
        return invade($job)->viewables->pluck('id')->toArray() === $collection->pluck('id')->toArray();
    });
});
