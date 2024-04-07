<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guest', function () {
    $response = $this->post(route('profile.verified.update'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('update verified', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'is_company_verified' => false,
        'github_username' => 'test',
    ]);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [
                        'sponsorshipTier' => [
                            'monthlyPriceInDollars' => 9,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->actingAs($user)->post(route('profile.verified.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your account has been verified.');

    $user->refresh();

    expect($user->is_verified)->toBeTrue()
        ->and($user->is_company_verified)->toBeFalse();
});

test('update non verified because does not sponsor', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'github_username' => 'test',
    ]);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                ],
            ],
        ]),
    ]);

    $response = $this->actingAs($user)->post(route('profile.verified.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your account is not verified yet.');

    $user->refresh();

    expect($user->is_verified)->toBeFalse();
});

test('update non verified because does not have github username', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'github_username' => null,
    ]);

    $response = $this->actingAs($user)->post(route('profile.verified.update'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your account is not verified yet.');

    $user->refresh();

    expect($user->is_verified)->toBeFalse();
});
