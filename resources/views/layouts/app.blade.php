<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-gray-950 font-sans antialiased">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <div class="flex-grow">
                @include('layouts.navigation')

                @if (isset($title))
                    <div class="mb-12 mt-14 flex flex-col items-center">
                        <div class="w-full max-w-md px-2">
                            <h1 class="text-2xl font-medium text-slate-200">
                                {{ $title }}
                            </h1>
                        </div>
                    </div>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>

            @include('components.footer')
        </div>
        @livewireScriptConfig
    </body>
</html>
