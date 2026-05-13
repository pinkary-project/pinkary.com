<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ __('Verify your email') }}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
            {{ __('Before getting started, please verify your email address by clicking the link we just sent you.') }}
        </p>
    </div>

    <div class="mb-4 text-sm text-gray-500">
        {{ __('Accounts are required to be verified before they can be used. Non-verified accounts will be automatically deleted after 24 hours.') }}
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-5 rounded-[1.25rem] border border-pink-500/20 bg-pink-500/10 px-4 py-3 text-sm text-pink-300">
            {{ __('A fresh verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form
            method="POST"
            action="{{ route('verification.send') }}"
            onsubmit="event.submitter.disabled = true"
            class="w-full sm:w-auto"
        >
            @csrf

            <div>
                <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20 sm:w-auto">
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form
            method="POST"
            action="{{ route('logout') }}"
        >
            @csrf

            <button
                type="submit"
                class="text-sm font-medium text-pink-500 transition hover:text-pink-400 focus:outline-none"
            >
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
