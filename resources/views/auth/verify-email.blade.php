<x-guest-layout>
    <section class="space-y-6">
        <div>
            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                Verify email
            </div>
            <h2 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Check your inbox.</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">
                {{ __('Accounts are required to be verified before they can be used. Non-verified accounts will be automatically deleted after 24 hours.') }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form
                method="POST"
                action="{{ route('verification.send') }}"
            >
                @csrf

                <div>
                    <x-primary-button class="justify-center rounded-full px-5 py-3">
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
                    class="rounded-full border border-slate-200/70 bg-slate-50/80 px-4 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-300 dark:hover:bg-slate-950 dark:hover:text-white"
                >
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </section>
</x-guest-layout>
