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

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->assertSet('enabled', false);
    $component->call('enableTwoFactorAuthentication');
    $component->assertSet('showingQrCode', true);
    $component->assertSet('showingRecoveryCodes', true);
    $component->assertSet('enabled', true);
});

it('can disable two factor authentication', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
    ]);

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->assertSet('enabled', true);
    $component->call('disableTwoFactorAuthentication');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', false);
    $component->assertSet('enabled', false);
});

it('can regenerate recovery codes', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
    ]);

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('regenerateRecoveryCodes');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', true);
});

it('can show recovery codes', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
    ]);

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('showRecoveryCodes');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', true);
});
