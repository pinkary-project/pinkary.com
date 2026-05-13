<x-guest-layout>
    @section('head')
        @turnstileScripts()
    @endsection

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ __('Create your account') }}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
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
                class="text-gray-400"
            />
            <x-text-input
                id="name"
                class="mt-2 block w-full rounded-md border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white shadow-none placeholder:text-gray-600 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20"
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
                class="text-gray-400"
            />
            <x-text-input
                id="username"
                class="mt-2 block w-full rounded-md border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white shadow-none placeholder:text-gray-600 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20"
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
                class="text-gray-400"
            />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-md border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white shadow-none placeholder:text-gray-600 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20"
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
                class="text-gray-400"
            />

            <x-text-input
                id="password"
                class="mt-2 block w-full rounded-md border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white shadow-none placeholder:text-gray-600 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20"
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
                class="text-gray-400"
            />

            <x-text-input
                id="password_confirmation"
                class="mt-2 block w-full rounded-md border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white shadow-none placeholder:text-gray-600 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20"
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
                <div class="flex justify-center rounded-[1.5rem] border border-slate-800/30 bg-[#0b1324] px-4 py-4">
                    <x-turnstile data-theme="auto"/>
                </div>
            @endif

            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <label for="terms" class="flex items-start gap-3 text-sm text-gray-400">
                    <x-checkbox
                    id="terms"
                    name="terms"
                    class="mt-0.5 rounded border-white/10 bg-white/5 text-pink-500 shadow-none focus:ring-4 focus:ring-pink-500/20 focus:ring-offset-0"
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
        <div class="border-t border-white/5"></div>
    </div>

    <div class="text-center text-sm text-gray-500">
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
