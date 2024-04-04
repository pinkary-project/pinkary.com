<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Http;

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

it('does not display the current version of the app if GitHub call fails', function () {
    Http::fake([
        'github.com/*' => Http::response([], 500),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('v1.0.0');
});

it('does not display the current version of the app if the response is empty', function () {
    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'repository' => [
                    'releases' => [
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('v1.0.0');
});

it('displays the current version of the app', function () {
    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'repository' => [
                    'releases' => [
                        'nodes' => [
                            [
                                'tagName' => 'v1.0.0',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('v1.0.0');
});
