<x-guest-layout>
    <section class="space-y-6">
        <div>
            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                Log In
            </div>

            <h2 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
                Welcome back.
            </h2>

            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">
                Pick up where you left off across the feed, profiles, and conversations already live on Pinkary.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('login') }}"
            onsubmit="event.submitter.disabled = true"
            class="space-y-4"
        >
            @csrf

            <div>
                <x-input-label
                    for="email"
                    :value="__('Email')"
                />
                <x-text-input
                    id="email"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
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

            <div>
                <x-input-label
                    for="password"
                    :value="__('Password')"
                />

                <div
                    class="relative mt-2"
                    x-data="{ showPassword: false }"
                >
                    <x-text-input
                        id="password"
                        class="block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 pr-10 dark:!border-slate-800 dark:!bg-slate-950/80"
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

            <div class="rounded-[1.25rem] border border-slate-200/70 bg-slate-50/80 px-4 py-3 dark:border-slate-800/70 dark:bg-slate-900/70">
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

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                @if (Route::has('password.request'))
                    <a
                        class="text-sm text-slate-600 underline hover:no-underline dark:text-slate-300"
                        href="{{ route('password.request') }}"
                        wire:navigate
                    >
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="justify-center rounded-full px-5 py-3">
                    {{ __('Log In') }}
                </x-primary-button>
            </div>
        </form>

        <div class="rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-4 text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400">
            Don't have an account?
            <a
                href="{{ route('register') }}"
                class="font-medium text-slate-900 underline hover:no-underline dark:text-white"
                wire:navigate
            >
                {{ __('Sign up here') }}
            </a>
        </div>
    </section>
</x-guest-layout>
