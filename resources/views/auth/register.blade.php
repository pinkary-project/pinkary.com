<x-guest-layout>
    @section('head')
        <script async src="https://www.google.com/recaptcha/api.js"></script>
    @endsection

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
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
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="timezone" :value="__('Timezone')" />
            <x-select-input
                id="timezone"
                class="mt-1 block w-full"
                name="timezone"
                :options="\App\Rules\ValidTimezone::timezones()"
                :value="old('timezone')"
                required
                autocomplete="timezone"
            />
            <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
        </div>

        <div x-data="{ password: '', confirmPassword: '' }">
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input
                    id="password"
                    class="mt-1 block w-full"
                    type="password"
                    x-model="password"
                    name="password"
                    required
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    type="password"
                    x-model="confirmPassword"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <x-input-error
                x-show="password && confirmPassword && password !== confirmPassword"
                :messages="__('validation.confirmed', ['attribute' => 'password'])"
                class="mt-2"
            />
        </div>

        <div class="g-recaptcha mt-4" data-sitekey="{{ config('services.recaptcha.key') }}" data-theme="dark"></div>

        @if ($errors->has('g-recaptcha-response'))
            <x-input-error :messages="'The reCAPTCHA is required.'" class="mt-2" />
        @endif

        <div class="mt-4 flex items-center justify-end space-x-3.5 text-sm">
            <div>
                <span class="text-slate-500">Already have an account?</span>

                <a
                    class="rounded-md text-sm text-slate-200 underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                    href="{{ route('login') }}"
                    wire:navigate
                >
                    {{ __(' Sign in here') }}
                </a>
            </div>

            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
