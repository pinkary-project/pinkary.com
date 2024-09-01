<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body
        class="font-sans antialiased bg-center bg-repeat bg-slate-950 text-slate-50"
        style="background-image: url({{ asset('/img/dots.svg') }})"
    >
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="flex flex-col min-h-screen">
            <div class="flex-grow w-full mx-auto sm:w-auto sm:ml-3 sm:mr-3">
                @include('layouts.navigation')
                @if (isset($title))
                    <div class="flex flex-col items-center mt-20 mb-6 sm:mb-12">
                        <div class="w-full px-2 sm:max-w-md sm:px-0">
                            <h1 class="text-2xl font-medium font-mona text-slate-200">
                                {{ $title }}
                            </h1>
                        </div>
                    </div>
                @endif
                <main class="sm:mt-16">
                    {{ $slot }}
                </main>
                <x-image-lightbox />
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
