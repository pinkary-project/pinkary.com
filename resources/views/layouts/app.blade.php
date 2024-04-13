<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-slate-950 bg-center bg-repeat font-sans text-slate-50 antialiased" style="background-image: url({{ asset('/img/dots.svg') }})">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <div class="flex-grow ml-3 mr-3">
                @include('layouts.navigation')

                @if (isset($title))
                    <div class="mb-6 sm:mb-12 mt-20 flex flex-col items-center">
                        <div class="w-full max-w-md px-2 sm:px-0">
                            <h1 class="font-mona text-2xl font-medium text-slate-200">
                                {{ $title }}
                            </h1>
                        </div>
                    </div>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>

            <x-footer />
        </div>
        @livewireScriptConfig

        <script>
            window.onload = function () {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
                if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
                    axios.post('{{ route('profile.timezone.update') }}', { timezone })
                }
            }
        </script>
    </body>
</html>
