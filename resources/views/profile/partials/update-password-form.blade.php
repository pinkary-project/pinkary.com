<section>
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div x-data="{ password: '', confirmPassword: '' }">

            <div class="mt-4">
                <x-input-label for="update_password_password" :value="__('New Password')" />
                <x-text-input
                    id="update_password_password"
                    name="password"
                    type="password"
                    x-model="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    x-model="confirmPassword"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <x-input-error
                x-show="password && confirmPassword && password !== confirmPassword"
                :messages="__('validation.confirmed', ['attribute' => 'password'])"
                class="mt-2"
            />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
