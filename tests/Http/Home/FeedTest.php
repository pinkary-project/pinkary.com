<?php

declare(strict_types=1);

use App\Enums\UserDefaultFeed;
use App\Livewire\Home\Feed;
use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Feed')
        ->assertSeeLivewire(Feed::class);
});

it('can see the question create component when logged in with recent default feed', function () {
    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Recent]);

    $response = $this->actingAs($user)
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Create::class);
});

it('redirects authenticated user with following default feed to the following page on fresh load', function () {
    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Following]);

    $response = $this->actingAs($user)->get(route('home.feed'));

    $response->assertRedirect(route('home.following'));
});

it('redirects authenticated user with trending default feed to the trending page on fresh load', function () {
    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Trending]);

    $response = $this->actingAs($user)->get(route('home.feed'));

    $response->assertRedirect(route('home.trending'));
});

it('shows recent feed when navigating via wire:navigate regardless of default feed', function () {
    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Following]);

    $response = $this->actingAs($user)
        ->withHeader('X-Livewire-Navigate', '')
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Feed::class);
});

it('shows recent feed to guest regardless of default feed setting', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Feed::class);
});

it('shows recent feed on subsequent visits after initial redirect', function () {
    $user = User::factory()->create(['default_feed' => UserDefaultFeed::Following]);

    $this->actingAs($user)->get(route('home.feed'))->assertSessionHas('_home_redirected', true);

    $response = $this->actingAs($user)->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Feed::class);
});

it('can filter questions to those with a particular hashtag', function () {
    $questionWithHashtag = Question::factory()->create(['answer' => 'question 1 with a #hashtag']);

    Question::factory()->create(['answer' => 'question 2 without hashtags']);

    $component = Livewire::test(Feed::class, ['hashtag' => 'hashtag']);

    $component
        ->assertViewHas('questions', fn (Illuminate\Pagination\Paginator $paginator): bool => $paginator
            ->pluck('id')
            ->all() === [$questionWithHashtag->id]
        )
        ->assertSee('question 1')
        ->assertDontSee('question 2')
        ->assertDontSee('There are no questions to show.');
});
