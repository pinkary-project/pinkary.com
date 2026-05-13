<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ __('Confirm your password') }}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.confirm') }}"
        onsubmit="event.submitter.disabled = true"
        class="space-y-5"
    >
        @csrf

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
                autocomplete="current-password"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <div>
            <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
