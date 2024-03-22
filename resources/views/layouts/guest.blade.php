<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-gray-950 font-sans text-gray-400 antialiased">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="fixed right-0">
                    @if (! request()->routeIs('welcome'))
                        @include('layouts.navigation')
                    @endif
                </div>

                <div class="-mt-10 flex min-h-screen flex-col items-center justify-center sm:mx-2">
                    <div class="w-full max-w-md rounded-lg px-4 py-10 shadow-md sm:px-0">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            @include('components.footer')
        </div>
        @livewireScriptConfig
    </body>
</html>
