<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Explore\QuestionsForYou;
use App\Livewire\Explore\TrendingQuestions;
use App\Livewire\Users\Index;
use App\Models\User;

it('can see the explore "users" view', function () {
    $response = $this->get(route('explore.users'));

    $response->assertOk()
        ->assertSee('Explore')
        ->assertSeeLivewire(Index::class);
});

it('can see the explore "trending" view', function () {
    Queue::fake(IncrementViews::class);
    $response = $this->get(route('explore.trending'));

    $response->assertOk()
        ->assertSee('Trending')
        ->assertSeeLivewire(TrendingQuestions::class);
    Queue::assertPushed(IncrementViews::class);
});

it('can see the explore "for you" view', function () {
    Queue::fake(IncrementViews::class);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('explore.for_you'));

    $response->assertOk()
        ->assertSee('For you')
        ->assertSeeLivewire(QuestionsForYou::class);
    Queue::assertPushed(IncrementViews::class);
});

it('guest can see the explore "for you" view', function () {
    Queue::fake(IncrementViews::class);
    $response = $this->get(route('explore.for_you'));

    $response->assertOk()
        ->assertSee('Log in or sign up to access personalized content');
    Queue::assertNotPushed(IncrementViews::class);
});
