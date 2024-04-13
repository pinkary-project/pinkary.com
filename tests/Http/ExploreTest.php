<?php

declare(strict_types=1);

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
    $response = $this->get(route('explore.trending'));

    $response->assertOk()
        ->assertSee('Trending')
        ->assertSeeLivewire(TrendingQuestions::class);
});

it('can see the explore "for you" view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('explore.for_you'));

    $response->assertOk()
        ->assertSee('For you')
        ->assertSeeLivewire(QuestionsForYou::class);
});

it('guest can see the explore "for you" view', function () {
    $response = $this->get(route('explore.for_you'));

    $response->assertOk()
        ->assertSee('Log in or sign up to access personalized content');
});
