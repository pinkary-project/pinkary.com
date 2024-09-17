<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body
        class="bg-slate-950 bg-center bg-repeat font-sans text-slate-50 antialiased"
        style="background-image: url({{ asset('/img/dots.svg') }})"
    >
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="fixed right-0">
                    @if (! request()->routeIs('about'))
                        @include('layouts.navigation')
                    @endif
                </div>

                <div class="flex min-h-screen flex-col justify-center overflow-hidden">
                    {{ $slot }}
                </div>
            </main>

            <x-footer />
        </div>
        @livewireScriptConfig
    </body>
</html>
