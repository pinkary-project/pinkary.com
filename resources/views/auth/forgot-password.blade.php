<x-guest-layout>
    <div class="flex min-h-screen flex-col">
        <main class="flex-grow">
            <div class="fixed right-0 z-50">
                @if (!request()->routeIs('about'))
                    @include('layouts.navigation')
                @endif
            </div>

            <div>
                <a href="{{ route('home.feed') }}" wire:navigate class="mt-20 flex justify-center">
                    <h2 class="text-3xl font-['poppins'] font-bold">batuly</h2>
                </a>

                <div class="mx-auto w-full max-w-md px-4 py-10 sm:px-0">
                    <div class="mb-4 text-sm dark:text-slate-400 text-slate-500">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                                :value="old('email')" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Email Password Reset Link') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <x-footer />
    </div>
</x-guest-layout>
