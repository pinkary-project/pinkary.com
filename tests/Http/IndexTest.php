<?php

declare(strict_types=1);

use App\Models\User;

it('guest', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Pinkary')
        ->assertSee('One Link. All Your Socials.');
});

it('auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response
        ->assertOk()
        ->assertSee('Pinkary')
        ->assertSee('One Link. All Your Socials.');
});

it('displays login button', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Log In')
        ->assertDontSee('Your Profile');
});

it('displays "Your Profile" when logged in', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Your Profile')
        ->assertDontSee('Log In');
});

it('displays terms of service and privacy policy', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Terms')
        ->assertSee('Privacy Policy')
        ->assertSee('Support')
        ->assertSee('Brand');
});
