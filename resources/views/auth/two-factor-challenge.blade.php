<x-guest-layout>
    <div x-data="{ recovery: false }" class="space-y-6">
        <div>
            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                Two-factor
            </div>
            <h2 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Confirm your sign-in.</h2>
            <div class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400" x-show="! recovery">
                {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
            </div>

            <div class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400" x-cloak x-show="recovery">
                {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-[1.25rem] border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
                <div class="font-medium">{{ __('Whoops! Something went wrong.') }}</div>
                <ul class="mt-3 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
            @csrf

            <div x-show="! recovery">
                <x-input-label for="code" value="{{ __('Code') }}" />
                <x-text-input id="code" class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
            </div>

            <div x-cloak x-show="recovery">
                <x-input-label for="recovery_code" value="{{ __('Recovery Code') }}" />
                <x-text-input id="recovery_code" class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" class="text-sm text-slate-600 underline cursor-pointer dark:text-slate-300"
                                x-show="! recovery"
                                x-on:click="
                                    recovery = true;
                                    $nextTick(() => { $refs.recovery_code.focus() })
                                ">
                    {{ __('Use a recovery code') }}
                </button>

                <button type="button" class="text-sm text-slate-600 underline cursor-pointer dark:text-slate-300"
                                x-cloak
                                x-show="recovery"
                                x-on:click="
                                    recovery = false;
                                    $nextTick(() => { $refs.code.focus() })
                                ">
                    {{ __('Use an authentication code') }}
                </button>

                <x-primary-button class="justify-center rounded-full px-5 py-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
