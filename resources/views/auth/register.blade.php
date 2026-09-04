<x-guest-layout>
    @section('head')
        <x-turnstile.scripts />
    @endsection

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
            {{ __('Create your account') }}
        </h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ __('Set up your Pinkary profile and start sharing your links and conversations.') }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('register') }}"
        onsubmit="event.submitter.disabled = true"
        x-data="passwordRegistration"
        class="space-y-5"
    >
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" class="text-slate-600 dark:text-slate-400" />
            <x-text-input
                id="name"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" class="text-slate-600 dark:text-slate-400" />
            <x-text-input
                id="username"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="text"
                name="username"
                :value="old('username')"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-600 dark:text-slate-400" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-5">
            <div>
                <x-input-label for="password" :value="__('Password')" class="text-slate-600 dark:text-slate-400" />

                <div class="relative" x-data="{ showPassword: false }">
                    <x-text-input
                        id="password"
                        class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 pr-14 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        x-model="password"
                        x-on:focus="passwordFocused = true"
                        x-on:blur="passwordFocused = false"
                        aria-describedby="password-strength"
                        name="password"
                        required
                        autocomplete="new-password"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button
                            type="button"
                            x-on:click="showPassword = ! showPassword"
                            x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                            x-bind:aria-pressed="showPassword"
                            class="-mr-2 rounded-md p-3 text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-pink-500/20 dark:text-gray-500 dark:hover:text-gray-300"
                        >
                            <x-icons.eye x-show="showPassword" class="size-5" />
                            <x-icons.eye-off x-show="! showPassword" class="size-5" />
                        </button>
                    </div>
                </div>

                <div
                    id="password-strength"
                    x-cloak
                    x-show="password.length > 0 && passwordFocused"
                    class="mt-2 flex items-center gap-3 rounded-lg border border-slate-200/80 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/5"
                    aria-live="polite"
                >
                    <span class="shrink-0 text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Password strength') }}</span>
                    <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                        <div
                            class="h-full rounded-full transition-all duration-300"
                            x-bind:class="passwordStrength().color"
                            x-bind:style="`width: ${passwordStrength().score * 25}%`"
                        ></div>
                    </div>
                    <span
                        class="shrink-0 text-xs font-medium"
                        x-text="passwordStrength().label"
                        x-bind:class="passwordStrength().textColor"
                    ></span>
                </div>

                <p
                    id="password-breach-error"
                    x-cloak
                    x-show="breachCheckStatus === 'compromised'"
                    class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                    role="alert"
                >
                    {{ __('This password was found in a data breach. Try a stronger one.') }}
                </p>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label
                    for="password_confirmation"
                    :value="__('Confirm Password')"
                    class="text-slate-600 dark:text-slate-400"
                />

                <div class="relative" x-data="{ showPassword: false }">
                    <x-text-input
                        id="password_confirmation"
                        class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 pr-14 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        x-bind:aria-invalid="passwordConfirmation.length > 0 && password !== passwordConfirmation"
                        x-model="passwordConfirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button
                            type="button"
                            x-on:click="showPassword = ! showPassword"
                            x-bind:aria-label="
                                showPassword ? 'Hide password confirmation' : 'Show password confirmation'
                            "
                            x-bind:aria-pressed="showPassword"
                            class="-mr-2 rounded-md p-3 text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-pink-500/20 dark:text-gray-500 dark:hover:text-gray-300"
                        >
                            <x-icons.eye x-show="showPassword" class="size-5" />
                            <x-icons.eye-off x-show="! showPassword" class="size-5" />
                        </button>
                    </div>
                </div>

                <p
                    x-cloak
                    x-show="passwordConfirmation.length > 0 && password !== passwordConfirmation"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                    role="alert"
                >
                    {{ __('The password confirmation does not match.') }}
                </p>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="space-y-4">
            @if (App::environment(['production', 'testing']))
                <div class="flex justify-center rounded-3xl border border-slate-200/80 bg-slate-50 px-4 py-4 dark:border-slate-800/30 dark:bg-[#0b1324]">
                    <x-turnstile data-theme="auto" />
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                <label for="terms" class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                    <x-checkbox
                        id="terms"
                        name="terms"
                        class="mt-0.5 rounded border-slate-200/80 bg-white text-pink-500 shadow-none focus:ring-4 focus:ring-pink-500/20 focus:ring-offset-0 dark:border-white/10 dark:bg-white/5"
                    />
                    <span>
                        {{ __('By signing up, I confirm that I am at least 18 years old and accept the') }}
                        <a
                            target="_blank"
                            href="{{ route('terms') }}"
                            class="text-pink-500 transition hover:text-pink-400"
                        >{{ __('Terms of Service') }}</a>
                        {{ __('and') }}
                        <a
                            target="_blank"
                            href="{{ route('privacy') }}"
                            class="text-pink-500 transition hover:text-pink-400"
                            >{{ __('Privacy Policy') }}</a
                        >{{ __('.') }}
                    </span>
                </label>
            </div>

            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        @if ($errors->has('cf-turnstile-response'))
            <x-input-error :messages="'The reCAPTCHA is required.'" class="mt-2" />
        @endif

        <div>
            <x-primary-button
                x-bind:disabled="! canSubmit()"
                x-bind:aria-disabled="! canSubmit()"
                class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20 disabled:cursor-not-allowed disabled:border-pink-300 disabled:bg-pink-300 disabled:text-white/80 disabled:hover:bg-pink-300 dark:disabled:border-pink-900/60 dark:disabled:bg-pink-900/60"
            >
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-slate-200/80 dark:border-white/5"></div>
    </div>

    <div class="text-center text-sm text-slate-500 dark:text-slate-400">
        {{ __('Already have an account?') }}
        <a class="font-medium text-pink-500 transition hover:text-pink-400" href="{{ route('login') }}" wire:navigate>
            {{ __('Sign in here') }}
        </a>
    </div>
</x-guest-layout>
