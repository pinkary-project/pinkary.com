<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="bg-slate-950 bg-center bg-repeat font-sans text-slate-50 antialiased" style="background-image: url({{ asset('/img/dots.svg') }})">
        <livewire:flash-messages.show />

        <div class="flex min-h-screen flex-col">
            <div class="flex-grow">
                @include('layouts.navigation')

                @if (isset($title))
                    <div class="mb-12 mt-14 flex flex-col items-center">
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
            var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
                fetch('{{ route('profile.timezone.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({timezone})
                });
            }
        </script>
    </body>
</html>
