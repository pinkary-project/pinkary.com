<x-guest-layout>
    @section('head')
        @turnstileScripts()
    @endsection
    <section class="space-y-6">
        <div>
            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                Register
            </div>

            <h2 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
                Create your Pinkary account.
            </h2>

            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">
                Set up your profile, collect your links, and join the existing questions and answers already happening on the platform.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('register') }}"
            class="space-y-4"
        >
            @csrf

            <div>
                <x-input-label
                    for="name"
                    :value="__('Name')"
                />
                <x-text-input
                    id="name"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                />
                <x-input-error
                    :messages="$errors->get('name')"
                    class="mt-2"
                />
            </div>

            <div>
                <x-input-label
                    for="username"
                    :value="__('Username')"
                />
                <x-text-input
                    id="username"
                    class="mt-2 block w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 px-4 py-3 dark:!border-slate-800 dark:!bg-slate-950/80"
                    type="text"
                    name="username"
                    :value="old('username')"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error
                    :messages="$errors->get('username')"
                    class="mt-2"
                />
            </div>

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
                    :value="old('email')"
                    required
                    autocomplete="email"
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

            <div class="rounded-[1.25rem] border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/70 dark:bg-slate-900/70">
                @if (App::environment(['production', 'testing']))
                    <div class="mb-4 flex justify-center">
                        <x-turnstile data-theme="auto"/>
                    </div>
                @endif

                <div class="flex items-start">
                    <input
                        id="terms"
                        name="terms"
                        type="checkbox"
                        class="mr-2 mt-1 h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-600"
                    />

                    <x-input-label for="terms">
                        By signing up, I confirm that I am at least 18 years old and accept the
                        <a
                            target="_blank"
                            href="{{ route('terms') }}"
                            class="text-pink-500 underline hover:no-underline"
                            >Terms of Service</a
                        >
                        and
                        <a
                            target="_blank"
                            href="{{ route('privacy') }}"
                            class="text-pink-500 underline hover:no-underline"
                            >Privacy Policy</a
                        >.
                    </x-input-label>
                </div>

                <x-input-error
                    :messages="$errors->get('terms')"
                    class="mt-2"
                />
            </div>

            @if ($errors->has('cf-turnstile-response'))
                <x-input-error
                    :messages="'The reCAPTCHA is required.'"
                    class="mt-2"
                />
            @endif

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between text-sm">
                <div>
                    <span class="text-slate-500">Already have an account?</span>

                    <a
                        class="rounded-md font-medium text-slate-900 underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:text-white"
                        href="{{ route('login') }}"
                        wire:navigate
                    >
                        {{ __(' Sign in here') }}
                    </a>
                </div>

                <x-primary-button class="justify-center rounded-full px-5 py-3">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    </section>
</x-guest-layout>
