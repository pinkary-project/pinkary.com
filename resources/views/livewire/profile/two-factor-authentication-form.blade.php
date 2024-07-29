<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Two factor authentication') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </p>
    </header>

    <section>
        <h3 class="text-lg font-medium text-slate-300">
            @if ($enabled)
                {{ __('You have enabled two factor authentication.') }}
            @else
                {{ __('You have not enabled two factor authentication.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-slate-500">
            <p>
                {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
            </p>
        </div>

        @if ($enabled)
            @if ($showingQrCode)
                <div class="mt-4 max-w-xl text-sm text-slate-500">
                    <p class="font-semibold">
                        {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                    </p>
                </div>

                <div class="mt-4 p-2 inline-block bg-white">
                    {!! $user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-4 max-w-xl text-sm text-slate-500">
                    <p class="font-semibold">
                        {{ __('Setup Key') }}: {{ decrypt($user->two_factor_secret) }}
                    </p>
                </div>
            @endif

            @if ($showingRecoveryCodes)
                <div class="mt-4 max-w-xl text-sm text-slate-500">
                    <p class="font-semibold">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>
                </div>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-900 text-gray-100 rounded-lg">
                    @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5">
            @if (!$enabled)
                <x-primary-button type="button" wire:loading.attr="disabled"
                    wire:click="enableTwoFactorAuthentication">
                    {{ __('Enable') }}
                </x-primary-button>
            @else
                @if ($showingRecoveryCodes)
                    <x-secondary-button class="me-3" wire:click="regenerateRecoveryCodes">
                        {{ __('Regenerate Recovery Codes') }}
                    </x-secondary-button>
                @else
                    <x-secondary-button class="me-3" wire:click="showRecoveryCodes">
                        {{ __('Show Recovery Codes') }}
                    </x-secondary-button>
                @endif

                <x-danger-button wire:loading.attr="disabled" wire:click="disableTwoFactorAuthentication">
                    {{ __('Disable') }}
                </x-danger-button>
            @endif
        </div>
    </section>
</section>
