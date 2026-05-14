<x-guest-layout>
    <div x-data="{ recovery: false }">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                {{ __('Two-factor challenge') }}
            </h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" x-show="! recovery">
                {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
            </p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" x-cloak x-show="recovery">
                {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-[1.25rem] border border-red-500/20 bg-red-500/10 px-4 py-3">
                <div class="font-medium text-red-700 dark:text-red-300">{{ __('Whoops! Something went wrong.') }}</div>
                <ul class="mt-3 list-disc list-inside text-sm text-red-700 dark:text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.login') }}" onsubmit="event.submitter.disabled = true" class="space-y-5">
            @csrf

            <div x-show="! recovery">
                <x-input-label for="code" value="{{ __('Code') }}" class="text-slate-600 dark:text-slate-400" />
                <x-text-input id="code" class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
            </div>

            <div x-cloak x-show="recovery">
                <x-input-label for="recovery_code" value="{{ __('Recovery Code') }}" class="text-slate-600 dark:text-slate-400" />
                <x-text-input id="recovery_code" class="mt-2 block w-full rounded-md border-slate-200/80 bg-white px-3 py-2.5 text-sm text-slate-950 shadow-none placeholder:text-slate-400 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-600" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" class="text-left text-sm font-medium text-pink-500 transition hover:text-pink-400 sm:text-center"
                                x-show="! recovery"
                                x-on:click="
                                    recovery = true;
                                    $nextTick(() => { $refs.recovery_code.focus() })
                                ">
                    {{ __('Use a recovery code') }}
                </button>

                <button type="button" class="text-left text-sm font-medium text-pink-500 transition hover:text-pink-400 sm:text-center"
                                x-cloak
                                x-show="recovery"
                                x-on:click="
                                    recovery = false;
                                    $nextTick(() => { $refs.code.focus() })
                                ">
                    {{ __('Use an authentication code') }}
                </button>

                <x-primary-button class="w-full justify-center rounded-md border-pink-500 bg-pink-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-600 focus:ring-4 focus:ring-pink-500/20 sm:w-auto">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
