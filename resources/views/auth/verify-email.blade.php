<x-guest-layout>
    <div class="mb-4 text-sm text-slate-400">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    <div class="mb-4 text-sm text-slate-400">
        {{ __('Accounts are required to be verified before they can be used. Non-verified accounts will be automatically deleted after 24 hours.') }}
    </div>

    <div class="mt-4 flex items-center justify-between">
        <form
            method="POST"
            action="{{ route('verification.send') }}"
        >
            @csrf

            <div>
                <x-primary-button>
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
                class="rounded-md text-sm text-slate-400 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
