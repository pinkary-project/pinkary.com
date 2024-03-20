<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-gray-950 font-sans text-gray-50 antialiased">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col font-welcome">
            <main class="flex-grow">
                <div class="fixed right-0">
                    @if (! request()->routeIs('welcome'))
                        @include('layouts.navigation')
                    @endif
                </div>

                <div class="flex min-h-screen flex-col items-center justify-center sm:mx-2">
                    {{ $slot }}
                </div>
            </main>

            @include('components.footer')
        </div>
        @livewireScriptConfig
    </body>
</html>
