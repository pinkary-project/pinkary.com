<?php

declare(strict_types=1);

use App\Models\User;

test('guest can update timezone', function (): void {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Europe/Madrid',
    ]);

    $response->assertStatus(200);
});

test('logged user can update timezone', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('profile.timezone.update'), [
        'timezone' => 'Europe/Madrid',
    ]);

    $response->assertStatus(200);
});

test('timezone must be valid', function (): void {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Nuno/Maduro',
    ]);

    $response->assertStatus(302);
});
