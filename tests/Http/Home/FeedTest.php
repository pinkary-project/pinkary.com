<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Home\Feed;
use App\Livewire\Questions\Create;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Feed')
        ->assertSeeLivewire(Feed::class);
});

it('can see the question create component when logged in', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Create::class);
});

it('does increment views', function () {
    Queue::fake(IncrementViews::class);

    $this->get(route('home.feed'));

    Queue::assertPushed(IncrementViews::class);
});
