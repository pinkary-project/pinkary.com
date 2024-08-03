<?php

declare(strict_types=1);

use App\Livewire\Profile\TwoFactorAuthenticationForm;
use App\Models\User;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

it('renders', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(TwoFactorAuthenticationForm::class);

    $component->assertOk();
});

it('can enable two factor authentication', function () {
    $user = User::factory()->create();
    session()->put('auth.password_confirmed_at', time());

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->assertSet('enabled', false);
    $component->call('enableTwoFactorAuthentication');
    $component->assertSet('showingQrCode', true);
    $component->assertSet('showingConfirmation', true);
    $component->assertSet('showingRecoveryCodes', false);
    $component->assertSet('enabled', true);

    $tfaEngine = app(Google2FA::class);
    $otp = $tfaEngine->getCurrentOtp(decrypt($user->refresh()->two_factor_secret));

    $component->set('code', $otp);
    $component->call('confirmTwoFactorAuthentication');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', true);
    $component->assertSet('enabled', true);
    $component->assertSet('code', null);
});

it('can not enable two factor authentication with invalid code', function () {
    $user = User::factory()->create();
    session()->put('auth.password_confirmed_at', time());
    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->assertSet('enabled', false);
    $component->call('enableTwoFactorAuthentication');
    $component->assertSet('showingQrCode', true);
    $component->assertSet('showingConfirmation', true);
    $component->assertSet('showingRecoveryCodes', false);
    $component->assertSet('enabled', true);

    $component->set('code', 'invalid');
    $component->call('confirmTwoFactorAuthentication');
    $component->assertHasErrors('code');
    $component->assertSet('showingQrCode', true);
    $component->assertSet('showingConfirmation', true);
    $component->assertSet('showingRecoveryCodes', false);
    $component->assertSet('enabled', true);

    // Ensure the user is still not enabled
    $newComponent = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);
    $newComponent->assertSet('enabled', false);
});

it('can disable two factor authentication', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
        'two_factor_confirmed_at' => now(),
    ]);
    session()->put('auth.password_confirmed_at', time());

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->assertSet('enabled', true);
    $component->call('disableTwoFactorAuthentication');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', false);
    $component->assertSet('enabled', false);
    $component->assertSet('code', null);
});

it('can regenerate recovery codes', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
        'two_factor_confirmed_at' => now(),
    ]);
    session()->put('auth.password_confirmed_at', time());
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
        'two_factor_confirmed_at' => now(),
    ]);
    session()->put('auth.password_confirmed_at', time());

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('showRecoveryCodes');
    $component->assertSet('showingQrCode', false);
    $component->assertSet('showingConfirmation', false);
    $component->assertSet('showingRecoveryCodes', true);
});

it('dispatches confirm-password event when password is not confirmed', function () {
    $user = User::factory()->create();
    session()->forget('auth.password_confirmed_at');

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('enableTwoFactorAuthentication');
    $component->assertDispatched('confirm-password', idToConfirm: 'enable-two-factor-authentication');

    $user->update([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['one', 'two'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $component = Livewire::actingAs($user->refresh())
        ->test(TwoFactorAuthenticationForm::class, ['enabled' => true]);

    $component->call('showRecoveryCodes');
    $component->assertDispatched('confirm-password', idToConfirm: 'show-recovery-codes');

    $component->call('regenerateRecoveryCodes');
    $component->assertDispatched('confirm-password', idToConfirm: 'generate-new-recovery-codes');

    $component->call('disableTwoFactorAuthentication');
    $component->assertDispatched('confirm-password', idToConfirm: 'disable-two-factor-authentication');
});

it('can not see codes if not enabled', function () {
    $user = User::factory()->create();
    session()->put('auth.password_confirmed_at', time());

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('showRecoveryCodes');
    $component->assertSet('showingRecoveryCodes', false);

    $component->call('regenerateRecoveryCodes');
    $component->assertSet('showingRecoveryCodes', false);
});

it('can not disable two factor authentication if not enabled', function () {
    $user = User::factory()->create();
    session()->put('auth.password_confirmed_at', time());

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthenticationForm::class);

    $component->call('disableTwoFactorAuthentication');
    $component->assertSet('enabled', false);
});
