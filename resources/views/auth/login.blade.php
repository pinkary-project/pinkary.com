<x-guest-layout>
    <div class="md:grid md:grid-cols-2 h-[100vh] overflow-hidden">
        <div class="p-10 bg-white md:px-44 flex flex-col h-full">
            <a href="{{ route('home.feed') }}" wire:navigate class="font-['poppins'] font-bold text-2xl">batuly</a>
            <div class="flex items-center flex-grow">
                <div class="w-full">
                    <div class="space-y-2">
                        <h2 class="font-bold text-3xl font-['poppins'] text-blue-500">Welcome Back</h2>
                        <p class="text-gray-500">Welcome back! Please enter your details</p>
                    </div>
                    <div class="mt-10">
                        <form method="POST" action="{{ route('login') }}" onsubmit="event.submitter.disabled = true">
                            @csrf

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="mt-1 block w-full h-12" type="email"
                                    name="email" :value="old('email')" required autofocus autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="password" :value="__('Password')" />

                                <div class="relative" x-data="{ showPassword: false }">
                                    <x-text-input id="password" class="mt-1 block h-12 w-full pr-10"
                                        x-bind:type="showPassword ? 'text' : 'password'" name="password" required
                                        autocomplete="current-password" />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <button type="button" x-on:click="showPassword = !showPassword">
                                            <x-icons.eye x-show="showPassword"
                                                class="size-5 text-slate-400 hover:text-pink-500" />
                                            <x-icons.eye-off x-show="!showPassword"
                                                class="size-5 text-slate-400 hover:text-pink-500" />
                                        </button>
                                    </div>
                                </div>

                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="mt-4 block">
                                <label for="remember_me" class="flex items-center">
                                    <x-checkbox id="remember_me" name="remember" />
                                    <span class="ml-2 text-sm text-slate-500">
                                        {{ __('Remember me') }}
                                    </span>
                                </label>
                            </div>

                            <div class="mt-4 flex items-center justify-end space-x-3.5">
                                @if (Route::has('password.request'))
                                    <a class="text-sm dark:text-slate-200 text-slate-800 underline hover:no-underline"
                                        href="{{ route('password.request') }}" wire:navigate>
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif

                                <x-primary-button>
                                    {{ __('Log In') }}
                                </x-primary-button>
                            </div>
                        </form>
                        <p class="text-sm text-gray-600 mt-5">Don't Have account ? <a href="{{ route('register') }}"
                                class="font-semibold text-blue-500">Register Now</a></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-10 justify-center items-center hidden md:flex">
            <img src="https://shanakhtmarketing.com/wp-content/uploads/2024/11/illust-delapan-1-1024x1024.png"
                alt="">
        </div>
    </div>
</x-guest-layout>
