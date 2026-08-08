<?php

declare(strict_types=1);

use App\Livewire\Profile\SwitchAccount;
use App\Models\User;
use Livewire\Livewire;

test('component renders available accounts', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $cookieValue = json_encode(['john' => true, 'jane' => true]);

    $component = Livewire::withCookies(['accounts' => $cookieValue])
        ->test(SwitchAccount::class);

    $component->assertViewHas('accounts', fn ($accounts): bool => $accounts->contains($user1) && $accounts->contains($user2));
});

test('component shows only usernames that exist in database', function (): void {
    User::factory()->create(['username' => 'john']);

    $cookieValue = json_encode(['john' => true, 'jane' => true]);

    $component = Livewire::withCookies(['accounts' => $cookieValue])
        ->test(SwitchAccount::class);

    $component->assertViewHas('accounts', fn ($accounts): bool => $accounts->count() === 1 && $accounts->first()->username === 'john');
});

test('switch method authenticates user and redirects', function (): void {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user2 = User::factory()->create(['username' => 'user2']);

    $cookieValue = json_encode(['user1' => true, 'user2' => true]);

    $this->actingAs($user1);

    $component = Livewire::withCookies(['accounts' => $cookieValue])
        ->test(SwitchAccount::class);

    $component->call('switch', 'user2')
        ->assertRedirect();

    expect(auth()->user()->username)->toBe('user2');
});

test('component handles empty accounts list', function (): void {
    $cookieValue = json_encode([]);

    $component = Livewire::withCookies(['accounts' => $cookieValue])
        ->test(SwitchAccount::class);

    $component->assertViewHas('accounts', fn ($accounts) => $accounts->isEmpty());
});
