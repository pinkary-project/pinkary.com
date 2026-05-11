<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="font-sans antialiased text-slate-950 dark:text-slate-50">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-slate-100 dark:bg-slate-950"></div>
            <div class="absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,_rgba(236,72,153,0.18),_transparent_58%)] dark:bg-[radial-gradient(circle_at_top,_rgba(244,114,182,0.18),_transparent_52%)]"></div>
            <div class="absolute left-[-8rem] top-20 h-56 w-56 rounded-full bg-pink-500/10 blur-3xl dark:bg-pink-500/10"></div>
            <div class="absolute right-[-4rem] top-40 h-64 w-64 rounded-full bg-sky-500/10 blur-3xl dark:bg-sky-500/10"></div>
            <div
                class="absolute inset-0 opacity-[0.14] dark:opacity-[0.08]"
                style="background-image: url({{ asset('/img/dots.svg') }}); background-position: center; background-repeat: repeat;"
            ></div>
        </div>

        <livewire:flash-messages.show />

        <div class="relative flex min-h-screen flex-col">
            <div class="mx-auto flex w-full max-w-[82rem] flex-1 flex-col px-4 py-6 sm:px-6 lg:px-8">
                <header class="flex items-center justify-between gap-4">
                    <a
                        href="{{ route('home.feed') }}"
                        wire:navigate
                        class="inline-flex items-center"
                    >
                        <x-pinkary-logo class="w-32 sm:w-36" />
                    </a>

                    <div class="flex items-center gap-2 text-sm">
                        <a
                            href="{{ route('home.feed') }}"
                            class="rounded-full px-4 py-2 text-slate-600 transition hover:bg-white/70 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900/70 dark:hover:text-white"
                            wire:navigate
                        >
                            Feed
                        </a>
                        <a
                            href="{{ route('about') }}"
                            class="rounded-full px-4 py-2 text-slate-600 transition hover:bg-white/70 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900/70 dark:hover:text-white"
                            wire:navigate
                        >
                            About
                        </a>
                    </div>
                </header>

                <main class="flex flex-1 items-center py-10 lg:py-14">
                    <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(24rem,30rem)] lg:items-center">
                        <section class="order-2 lg:order-1">
                            <div class="rounded-3xl border border-white/70 bg-white/80 p-6 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-8 lg:p-10">
                                <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                                    Pinkary
                                </div>

                                <h1 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl">
                                    Existing features, now with a sharper shell.
                                </h1>

                                <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-400 sm:text-base">
                                    Pinkary already has the essentials live: public profiles, questions, answers, follows, and links. This pass brings those pieces into the new visual language without inventing a new product area.
                                </p>

                                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/70 dark:bg-slate-900/70">
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Already live</p>
                                        <p class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Questions, answers, and updates</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/70 dark:bg-slate-900/70">
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Profiles</p>
                                        <p class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Public identities, follows, and link lists</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex flex-wrap gap-3 text-sm">
                                    <a
                                        href="{{ route('home.feed') }}"
                                        class="inline-flex items-center rounded-full bg-pink-500 px-4 py-2.5 font-medium text-white transition hover:bg-pink-400"
                                        wire:navigate
                                    >
                                        Explore feed
                                    </a>
                                    <a
                                        href="{{ route('register') }}"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 font-medium text-slate-700 transition hover:border-pink-500/30 hover:text-slate-950 dark:border-slate-800 dark:bg-slate-950/70 dark:text-slate-300 dark:hover:text-white"
                                        wire:navigate
                                    >
                                        Create account
                                    </a>
                                </div>
                            </div>
                        </section>

                        <div class="order-1 lg:order-2">
                            <div class="mx-auto w-full max-w-md rounded-3xl border border-white/70 bg-white/85 p-6 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/30 sm:p-8">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </main>
            </div>

            <x-footer />
        </div>
        @livewireScriptConfig
    </body>
</html>
