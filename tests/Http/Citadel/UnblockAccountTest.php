<?php

declare(strict_types=1);

use App\Filament\Pages\UnblockAccount;
use App\Models\BlockedAccount;
use App\Models\User;
use Livewire\Livewire;

test('auth', function (): void {
    $response = $this->get(UnblockAccount::getUrl());

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('is only accessible to nuno', function (): void {
    $user = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $this->actingAs($user);

    Livewire::test(UnblockAccount::class)->assertOk();
});

it('is not accessible to other users', function (): void {
    $user = User::factory()->create([
        'email' => 'nuno@laravel.com',
    ]);

    $response = $this->actingAs($user)->get(UnblockAccount::getUrl());

    $response->assertStatus(403);
});

it('can unblock an email', function (): void {
    $user = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $blocked = BlockedAccount::factory()->create([
        'email' => 'blocked@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(UnblockAccount::class)
        ->fillForm([
            'email' => 'blocked@example.com',
        ])
        ->call('unblock')
        ->assertNotified('Account unblocked.');

    $this->assertDatabaseMissing('blocked_accounts', ['email' => 'blocked@example.com']);
});

it('shows error when email is not in blocked list', function (): void {
    $user = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $this->actingAs($user);

    Livewire::test(UnblockAccount::class)
        ->fillForm([
            'email' => 'notblocked@example.com',
        ])
        ->call('unblock')
        ->assertNotified('Email not found in blocked list.');
});
