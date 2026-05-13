<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ __('Reset your password') }}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
            {{ __('Enter the email address tied to your Pinkary account and we will send you a secure reset link.') }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.email') }}"
        onsubmit="event.submitter.disabled = true"
        class="space-y-5"
    >
        @csrf

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
                autofocus
            />
            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <div>
            <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-white/5"></div>
    </div>

    <div class="text-center text-sm text-gray-500">
        {{ __('Remembered it?') }}
        <a
            href="{{ route('login') }}"
            class="font-medium text-pink-500 transition hover:text-pink-400"
            wire:navigate
        >
            {{ __('Back to sign in') }}
        </a>
    </div>
</x-guest-layout>
