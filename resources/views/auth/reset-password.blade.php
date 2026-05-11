<x-guest-layout>
    <section class="space-y-6">
        <div>
            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                Reset password
            </div>
            <h2 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Choose a new password.</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">Use the reset link you received to secure the account and get back into Pinkary.</p>
        </div>

        <form
            method="POST"
            action="{{ route('password.store') }}"
            class="space-y-4"
        >
            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}"
            />

            <div>
                <x-input-label
                    for="email"
                    :value="__('Email')"
                />
                <x-text-input
                    id="email"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
                    type="email"
                    name="email"
                    :value="old('email', $request->email)"
                    required
                    autofocus
                    autocomplete="username"
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
                />
                <x-text-input
                    id="password"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
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
                />

                <x-text-input
                    id="password_confirmation"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
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

            <div class="flex justify-end">
                <x-primary-button class="justify-center rounded-full px-5 py-3">
                    {{ __('Reset Password') }}
                </x-primary-button>
            </div>
        </form>
    </section>
</x-guest-layout>
