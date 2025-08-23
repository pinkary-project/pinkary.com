<?php

declare(strict_types=1);
use App\Models\User;

test('user can update their profile information', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user);

    $page = visit('/profile');

    $page
        ->assertValue('name', 'John Doe')
        ->assertValue('email', 'john@example.com');

    $page->fill('name', 'Jane Doe')
        ->fill('email', 'jane@example.com')
        ->press('@update-profile-button');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});
