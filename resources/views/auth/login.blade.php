<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ __('Log in') }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Continue to your Pinkary account.') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" onsubmit="event.submitter.disabled = true" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-600 dark:text-slate-400" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-slate-600 dark:text-slate-400" />

            <div class="relative" x-data="{ showPassword: false }">
                <x-text-input
                    id="password"
                    class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 pr-10 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button
                        type="button"
                        x-on:click="showPassword = ! showPassword"
                        class="rounded-md text-slate-400 transition hover:text-slate-600 focus:ring-4 focus:ring-pink-500/20 focus:outline-none dark:text-gray-500 dark:hover:text-gray-300"
                    >
                        <x-icons.eye x-show="showPassword" class="size-5" />
                        <x-icons.eye-off x-show="! showPassword" class="size-5" />
                    </button>
                </div>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="flex items-center">
                <x-checkbox
                    id="remember_me"
                    name="remember"
                    class="rounded border-slate-200/80 bg-white text-pink-500 shadow-none focus:ring-4 focus:ring-pink-500/20 focus:ring-offset-0 dark:border-white/10 dark:bg-white/5"
                />
                <span class="ml-2 text-sm text-slate-500 dark:text-slate-400"> {{ __('Remember me') }} </span>
            </label>

            @if (Route::has('password.request'))
                <a
                    class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
                    href="{{ route('password.request') }}"
                    wire:navigate
                >
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20">
                {{ __('Log In') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-slate-200/80 dark:border-white/5"></div>
    </div>

    <div class="text-center text-sm text-slate-500 dark:text-slate-400">
        Don't have an account?
        <a
            href="{{ route('register') }}"
            class="font-medium text-pink-500 transition hover:text-pink-400"
            wire:navigate
        >
            {{ __('Create one') }}
        </a>
    </div>
</x-guest-layout>
