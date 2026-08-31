<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
            {{ __('Reset your password') }}
        </h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
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
            <x-input-label for="email" :value="__('Email')" class="text-slate-600 dark:text-slate-400" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <div class="py-8">
        <div class="border-t border-slate-200/80 dark:border-white/5"></div>
    </div>

    <div class="text-center text-sm text-slate-500 dark:text-slate-400">
        {{ __('Remembered it?') }}
        <a href="{{ route('login') }}" class="font-medium text-pink-500 transition hover:text-pink-400" wire:navigate>
            {{ __('Back to sign in') }}
        </a>
    </div>
</x-guest-layout>
