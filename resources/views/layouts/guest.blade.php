<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-gray-900 font-sans text-gray-200 antialiased">
        <livewire:flash-messages.show />

        <div class="dark flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="mx-auto grid min-h-[calc(100vh-9rem)] w-full max-w-7xl xl:grid-cols-[18rem_minmax(0,1fr)_18rem]">
                    <aside class="hidden border-x border-white/5 bg-black/10 px-6 xl:flex xl:flex-col">
                        <a
                            href="{{ route('home.feed') }}"
                            wire:navigate
                            class="mt-6 flex"
                            aria-label="{{ config('app.name') }}"
                        >
                            <x-pinkary-logo class="h-12 w-auto" />
                        </a>

                        <div class="mt-12 space-y-6">
                            <div>
                                <p class="text-sm font-medium text-white">{{ config('app.name') }}</p>
                                <p class="mt-1 text-xs/6 text-gray-500">One link. All your socials.</p>
                            </div>

                            <nav class="-mx-2 space-y-1 text-sm font-semibold">
                                <a
                                    href="{{ route('home.feed') }}"
                                    wire:navigate
                                    class="flex rounded-md bg-gray-800 px-2 py-2 text-white"
                                >
                                    Home
                                </a>
                                <a
                                    href="{{ route('about') }}"
                                    wire:navigate
                                    class="flex rounded-md px-2 py-2 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                                >
                                    About
                                </a>
                                <a
                                    href="{{ route('support') }}"
                                    wire:navigate
                                    class="flex rounded-md px-2 py-2 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                                >
                                    Support
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <div class="flex min-h-full flex-col justify-center border-white/5 px-4 py-10 sm:px-6 xl:border-r xl:px-8">
                        <a
                            href="{{ route('home.feed') }}"
                            wire:navigate
                            class="mb-10 flex justify-center xl:hidden"
                            aria-label="{{ config('app.name') }}"
                        >
                            <x-pinkary-logo class="h-12 w-auto" />
                        </a>

                        <div class="mx-auto w-full max-w-md rounded-md border border-white/5 bg-black/10 p-6 shadow-2xl shadow-black/20 sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>

                    <aside class="hidden bg-black/10 xl:flex xl:flex-col xl:border-r xl:border-white/5">
                        <div class="border-b border-white/5 p-8">
                            <p class="text-sm font-medium text-white">Welcome back</p>
                            <p class="mt-1 text-xs/6 text-gray-500">Sign in and keep your Pinkary profile moving.</p>
                        </div>

                        <div class="mt-auto border-t border-white/5 p-8 text-xs text-gray-500">
                            <p class="font-medium text-gray-200">{{ config('app.name') }}</p>
                            <p class="mt-1">One link. All your socials.</p>
                        </div>
                    </aside>
                </div>
            </main>

            <x-footer />
        </div>
        @livewireScriptConfig
    </body>
</html>
