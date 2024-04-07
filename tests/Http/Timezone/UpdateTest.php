<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\Requests\TimezoneUpdateRequest;
use App\Models\User;

test('guest can update timezone', function () {
    $response = $this->post(route('timezone.update'), [
        'timezone' => 'Europe/Madrid',
    ]);

    $response->assertStatus(200);
});

test('logged user can update timezone', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('timezone.update'), [
        'timezone' => 'Europe/Madrid',
    ]);

    $response->assertStatus(200);
});

test('update timezone is rate limited', function () {
    for ($i = 0; $i < TimezoneUpdateRequest::MAX_ATTEMPTS; $i++) {
        $this->post(route('timezone.update'), [
            'timezone' => 'Europe/Madrid',
        ])->assertStatus(200);
    }

    $this->post(route('timezone.update'), [
        'timezone' => 'Europe/Madrid',
    ])->assertStatus(302)->assertSessionHasErrors([
        'timezone',
    ]);
});

test('timezone must be valid', function () {
    $response = $this->post(route('timezone.update'), [
        'timezone' => 'Nuno/Maduro',
    ]);

    $response->assertStatus(302);
});
