<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-gray-950 bg-center bg-repeat font-sans text-gray-50 antialiased" style="background-image: url({{ asset('/img/dots.svg') }})">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="fixed right-0 z-50">
                    @if (! request()->routeIs('welcome'))
                        @include('layouts.navigation')
                    @endif
                </div>

                <div>
                    <a href="{{ route('welcome') }}" wire:navigate class="flex justify-center mt-20">
                        <x-pinkary-logo class="z-10 w-48" />
                    </a>

                    <div class="w-full max-w-md px-4 py-10 sm:px-0 mx-auto">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            @include('components.footer')
        </div>
        @livewireScriptConfig
    </body>
</html>
