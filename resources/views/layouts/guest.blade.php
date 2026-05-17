<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-slate-100 font-sans text-slate-950 antialiased dark:bg-[#060c18] dark:text-slate-200">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="mx-auto grid min-h-[calc(100vh-9rem)] w-full max-w-7xl xl:grid-cols-[18rem_minmax(0,1fr)_18rem]">
                    <aside class="hidden border-x border-slate-200/70 bg-white/85 px-6 dark:border-slate-800/30 dark:bg-[#07101f]/80 xl:flex xl:flex-col">
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
                                <p class="text-sm font-medium text-slate-950 dark:text-white">{{ config('app.name') }}</p>
                                <p class="mt-1 text-xs/6 text-slate-500 dark:text-slate-500">One link. All your socials.</p>
                            </div>

                            <nav class="-mx-2 space-y-1 text-sm font-semibold">
                                <a
                                    href="{{ route('home.feed') }}"
                                    wire:navigate
                                    class="flex rounded-md bg-slate-950 px-2 py-2 text-white dark:bg-[#1a2438]"
                                >
                                    Home
                                </a>
                                <a
                                    href="{{ route('about') }}"
                                    wire:navigate
                                    class="flex rounded-md px-2 py-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white"
                                >
                                    About
                                </a>
                                <a
                                    href="{{ route('support') }}"
                                    wire:navigate
                                    class="flex rounded-md px-2 py-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white"
                                >
                                    Support
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <div class="flex min-h-full flex-col justify-center border-slate-200/70 px-4 py-10 dark:border-slate-800/30 sm:px-6 xl:border-r xl:px-8">
                        <a
                            href="{{ route('home.feed') }}"
                            wire:navigate
                            class="mb-10 flex justify-center xl:hidden"
                            aria-label="{{ config('app.name') }}"
                        >
                            <x-pinkary-logo class="h-12 w-auto" />
                        </a>

                        <div class="mx-auto w-full max-w-md rounded-md border border-slate-200/70 bg-white/95 p-6 shadow-xl shadow-slate-900/5 dark:border-slate-800/30 dark:bg-[#07101f]/95 dark:shadow-black/20 sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>

                    <aside class="hidden bg-white/85 dark:bg-[#07101f]/80 xl:flex xl:flex-col xl:border-r xl:border-slate-200/70 dark:xl:border-slate-800/30">
                        <div class="border-b border-slate-200/70 p-8 dark:border-slate-800/30">
                            <p class="text-sm font-medium text-slate-950 dark:text-white">Welcome back</p>
                            <p class="mt-1 text-xs/6 text-slate-500 dark:text-slate-500">Sign in and keep your Pinkary profile moving.</p>
                        </div>

                        <div class="mt-auto border-t border-slate-200/70 p-8 text-xs text-slate-500 dark:border-slate-800/30 dark:text-slate-500">
                            <p class="font-medium text-slate-950 dark:text-slate-200">{{ config('app.name') }}</p>
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
