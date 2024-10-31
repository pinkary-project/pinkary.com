<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body
        class="dark:bg-slate-950 bg-slate-100 bg-center bg-repeat font-sans dark:text-slate-50 text-slate-900 antialiased"
        style="background-image: url({{ asset('/img/dots.svg') }})"
    >
        <livewire:flash-messages.show />

        <div class="flex min-h-screen">
            <header class="hidden sm:block w-1/5 border-r dark:border-slate-800 border-slate-300 min-h-screen">
                <div class="sticky top-0">
                    @if (! request()->routeIs('about'))
                        @include('layouts.navigation')
                    @endif
                </div>
            </header>
            <div class="flex-grow">
                <main>
                    <a
                        href="{{ route('home.feed') }}"
                        wire:navigate
                        class="mt-12 flex justify-center"
                    >
                        <x-pinkary-logo class="z-10 w-48" />
                    </a>

                    <div class="mx-auto w-full max-w-md px-4 py-5 sm:px-0">
                        {{ $slot }}
                    </div>
                </main>
                <x-footer />
            </div>
        </div>
        @livewireScriptConfig
    </body>
</html>
