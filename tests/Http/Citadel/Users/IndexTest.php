<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;

test('auth', function (): void {
    $response = $this->get(UserResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('is only accessible to nuno', function (): void {
    $user = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $response = $this->actingAs($user)->get(UserResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(200)->assertSee('Users');
});

it('is not accessible to other users', function (): void {
    $user = User::factory()->create([
        'email' => 'nuno@laravel.com',
    ]);

    $response = $this->actingAs($user)->get(UserResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(403);
});
