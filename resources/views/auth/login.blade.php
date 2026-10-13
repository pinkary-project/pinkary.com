<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
            {{ auth()->check() ? __('Add Account') : __('Log in') }}
        </h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            @auth
                {{ __('Sign in with another Pinkary account to switch between them easily.') }}
            @else
                {{ __('Continue to your Pinkary account.') }}
            @endauth
        </p>
    </div>

    @auth
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-slate-200/80 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
            <img
                src="{{ auth()->user()->avatar_url }}"
                alt="{{ auth()->user()->username }}"
                class="{{ auth()->user()->is_company_verified ? 'rounded-md' : 'rounded-full' }} size-8 shrink-0"
            />
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Currently signed in as') }}</p>
                <p class="truncate text-sm font-semibold text-slate-950 dark:text-white">
                    {{ '@' . auth()->user()->username }}
                </p>
            </div>
        </div>
    @endauth

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

            <x-password-input
                id="password"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                name="password"
                required
                autocomplete="current-password"
            />

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
                {{ auth()->check() ? __('Add Account') : __('Log In') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-slate-200/80 dark:border-white/5"></div>
    </div>

    @guest
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
    @endguest
</x-guest-layout>
