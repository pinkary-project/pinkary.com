<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body
        class="bg-slate-950 bg-center bg-repeat font-sans text-slate-50 antialiased"
        style="background-image: url({{ asset('/img/dots.svg') }})"
    >
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="flex min-h-screen flex-col">
            <div class="mx-auto ml-3 mr-3 flex-grow">
                @include('layouts.navigation')
                <main class="mt-16">
                    {{ $slot }}
                </main>
            </div>

            @persist('footer')
                <x-back-to-top :offset="300" />
                <x-footer />
            @endpersist
        </div>
        @livewireScriptConfig

        <script>
            window.onload = function () {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
                if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
                    axios.post('{{ route('profile.timezone.update') }}', { timezone })
                }

                Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    fail(({ status, content, preventDefault }) => {
                        if (status === 419) {
                            preventDefault()
                        }
                    })
                })
            }
        </script>
    </body>
</html>
