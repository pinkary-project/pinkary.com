<x-guest-layout>
    <form
        method="POST"
        action="{{ route('login') }}"
        onsubmit="event.submitter.disabled = true"
    >
        @csrf

        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
            />
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
            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <div class="mt-4">
            <x-input-label
                for="password"
                :value="__('Password')"
            />

            <div 
                class="relative"
                x-data="{ showPassword: false }" 
            >
                <x-text-input 
                    id="password" 
                    class="mt-1 block w-full pr-10" 
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required 
                    autocomplete="current-password"
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button type="button" x-on:click="showPassword = !showPassword">
                        <x-icons.eye x-show="showPassword" class="size-5 text-slate-400 hover:text-pink-500" />
                        <x-icons.eye-off x-show="!showPassword" class="size-5 text-slate-400 hover:text-pink-500" />
                    </button>
                </div>
            </div>

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <div class="mt-4 block">
            <label
                for="remember_me"
                class="flex items-center"
            >
                <x-checkbox
                    id="remember_me"
                    name="remember"
                />
                <span class="ml-2 text-sm text-slate-500">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end space-x-3.5">
            @if (Route::has('password.request'))
                <a
                    class="text-sm dark:text-slate-200 text-slate-800 underline hover:no-underline"
                    href="{{ route('password.request') }}"
                    wire:navigate
                >
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
        <a
            href="{{ route('register') }}"
            class="dark:text-slate-200 text-slate-800 underline hover:no-underline"
            wire:navigate
        >
            {{ __('Sign up here') }}
        </a>
    </div>
</x-guest-layout>
