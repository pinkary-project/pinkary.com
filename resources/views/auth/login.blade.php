<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="mt-1 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="flex items-center">
                <x-checkbox id="remember_me" name="remember" />
                <span class="ml-2 text-sm text-gray-500">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="text-sm hover:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log In') }}
            </x-primary-button>
        </div>
    </form>

    <x-section-border />

    <div class="mt-4 flex items-center justify-center text-sm">
        <a href="{{ route('register') }}" class="hover:underline" wire:navigate>
            <span class="ml-2">
                {{ __('Don\'t have an account? Sign up here.') }}
            </span>
        </a>
    </div>
</x-guest-layout>
