<x-guest-layout>
    @section('head')
        @turnstileScripts()
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
        class="space-y-5"
    >
        @csrf

        <div>
            <x-input-label
                for="name"
                :value="__('Name')"
                class="text-slate-600 dark:text-slate-400"
            />
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
            <x-input-error
                :messages="$errors->get('name')"
                class="mt-2"
            />
        </div>

        <div>
            <x-input-label
                for="username"
                :value="__('Username')"
                class="text-slate-600 dark:text-slate-400"
            />
            <x-text-input
                id="username"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="text"
                name="username"
                :value="old('username')"
                required
                autocomplete="username"
            />
            <x-input-error
                :messages="$errors->get('username')"
                class="mt-2"
            />
        </div>

        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
                class="text-slate-600 dark:text-slate-400"
            />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="email"
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
                class="text-slate-600 dark:text-slate-400"
            />

            <x-text-input
                id="password"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <div>
            <x-input-label
                for="password_confirmation"
                :value="__('Confirm Password')"
                class="text-slate-600 dark:text-slate-400"
            />

            <x-text-input
                id="password_confirmation"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        <div class="space-y-4">

            @if (App::environment(['production', 'testing']))
                <div class="flex justify-center rounded-3xl border border-slate-200/80 bg-slate-50 px-4 py-4 dark:border-slate-800/30 dark:bg-[#0b1324]">
                    <x-turnstile data-theme="auto"/>
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
                    >{{ __('Privacy Policy') }}</a>{{ __('.') }}
                    </span>
                </label>
            </div>

            <x-input-error
                :messages="$errors->get('terms')"
                class="mt-2"
            />
        </div>

    @if ($errors->has('cf-turnstile-response'))
            <x-input-error
                :messages="'The reCAPTCHA is required.'"
                class="mt-2"
            />
        @endif

        <div>
            <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-slate-200/80 dark:border-white/5"></div>
    </div>

    <div class="text-center text-sm text-slate-500 dark:text-slate-400">
        {{ __('Already have an account?') }}
        <a
            class="font-medium text-pink-500 transition hover:text-pink-400"
            href="{{ route('login') }}"
            wire:navigate
        >
            {{ __('Sign in here') }}
        </a>
    </div>
</x-guest-layout>
