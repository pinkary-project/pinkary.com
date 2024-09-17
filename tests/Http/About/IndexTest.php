<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Process;

it('guest', function () {
    $response = $this->get('/about');

    $response
        ->assertOk()
        ->assertSee('Pinkary')
        ->assertSee('One Link. All Your Socials.');
});

it('auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/about');

    $response
        ->assertOk()
        ->assertSee('Pinkary')
        ->assertSee('One Link. All Your Socials.');
});

it('displays login button', function () {
    $response = $this->get('/about');

    $response
        ->assertOk()
        ->assertSee('Log In')
        ->assertDontSee('Your Profile');
});

it('displays "Your Profile" when logged in', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/about');

    $response
        ->assertOk()
        ->assertSee('Your Profile')
        ->assertDontSee('Log In');
});

it('displays terms of service and privacy policy', function () {
    $response = $this->get('/about');

    $response
        ->assertOk()
        ->assertSee('Terms')
        ->assertSee('Privacy Policy')
        ->assertSee('Support')
        ->assertSee('Brand');
});

it('displays the current version of the app', function () {
    Process::fake([
        '*' => Process::result(
            output: "v1.0.0\n",
        ),
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('v1.0.0');
});
