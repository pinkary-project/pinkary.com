<?php

declare(strict_types=1);

use App\Enums\HomePageTabs;
use App\Models\User;

it('have a default tab', function () {
    $newUser = User::factory()->create();
    expect($newUser->default_tab)->toBeString();
});

it('unauthorized user redirects to feed', function () {
    $response = $this->get(route('home'));
    $response->assertRedirect(route('home.feed'));
});

it('default tab is feed for new user', function () {
    $newUser = User::factory()->create();
    $response = $this->actingAs($newUser)->get(route('home'));
    $response->assertRedirect(route('home.feed'));
});

it('/ redirects to the selected default feed.', function () {
    $newUser = User::factory()->create();
    $response = $this->actingAs($newUser)->get(route('home'));

    $defaultTab = $newUser->default_tab;

    $response->assertRedirect(route('home.'.$defaultTab));
});

it('can update the default tab to following', function () {
    $newUser = User::factory()->create();

    $newUser->default_tab = HomePageTabs::Following->value;
    $newUser->save();

    $response = $this->actingAs($newUser)->get(route('home'));

    $response->assertRedirect(route('home.following'));
});
