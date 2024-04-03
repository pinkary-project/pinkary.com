<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email/Username')" />
            <x-text-input
                id="username"
                class="mt-1 block w-full"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="flex items-center">
                <x-checkbox id="remember_me" name="remember" />
                <span class="ml-2 text-sm text-slate-500">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end space-x-3.5">
            @if (Route::has('password.request'))
                <a class="text-sm text-slate-200 underline hover:no-underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log In') }}
            </x-primary-button>
        </div>
    </form>

    <x-section-border />

    <div class="mt-4 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-slate-200 underline hover:no-underline" wire:navigate>
            {{ __('Sign up here') }}
        </a>
    </div>
</x-guest-layout>
