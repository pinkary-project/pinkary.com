<?php

declare(strict_types=1);

use App\Enums\Feeds;
use App\Models\User;

it('have a default tab', function () {
    $newUser = User::factory()->create();
    expect($newUser->default_tab)->toBeString();
});

it('unauthorized user redirects to feed', function () {
    $response = $this->get(route('home'));
    $response->assertRedirect(route('home.recent'));
});

it('default tab is following for new user', function () {
    $newUser = User::factory()->create();
    $response = $this->actingAs($newUser)->get(route('home'));
    $response->assertRedirect(route('home.following'));
});

it('/ redirects to the selected default feed.', function () {
    $newUser = User::factory()->create();
    $response = $this->actingAs($newUser)->get(route('home'));

    $defaultTab = $newUser->default_tab;

    $response->assertRedirect(route('home.'.$defaultTab));
});

it('can update the default tab to following', function () {
    $newUser = User::factory()->create();

    $newUser->default_tab = Feeds::Following->value;
    $newUser->save();

    $response = $this->actingAs($newUser)->get(route('home'));

    $response->assertRedirect(route('home.following'));
});
