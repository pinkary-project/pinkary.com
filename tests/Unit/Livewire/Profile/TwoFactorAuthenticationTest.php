<?php

declare(strict_types=1);

use App\Livewire\Profile\TwoFactorAuthenticationForm;
use App\Models\User;
use Livewire\Livewire;

it('renders', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(TwoFactorAuthenticationForm::class);

    $component->assertOk();
});

it('can enable two factor authentication', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class)
        ->call('enableTwoFactorAuthentication')
        ->assertSet('showingQrCode', true)
        ->assertSet('showingRecoveryCodes', true);
});

it('can disable two factor authentication', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
    ]);

    Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class)
        ->call('disableTwoFactorAuthentication')
        ->assertSet('showingQrCode', false)
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', false);
});

it('can regenerate recovery codes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class)
        ->call('regenerateRecoveryCodes')
        ->assertSet('showingQrCode', false)
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', true);
});

it('can show recovery codes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class)
        ->call('showRecoveryCodes')
        ->assertSet('showingQrCode', false)
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', true);
});
