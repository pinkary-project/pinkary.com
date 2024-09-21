<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body
        class="dark:bg-slate-950 bg-slate-100 bg-center bg-repeat font-sans dark:text-slate-50 text-slate-950 antialiased"
        style="background-image: url({{ asset('/img/dots.svg') }})"
    >
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="flex min-h-screen flex-col">
            <div class="mx-auto ml-3 mr-3 flex-grow">
                @include('layouts.navigation')
                @if (isset($title))
                    <div class="mb-6 mt-20 flex flex-col items-center sm:mb-12">
                        <div class="w-full max-w-md px-2 sm:px-0">
                            <h1 class="font-mona text-2xl font-medium dark:text-slate-200 text-slate-900">
                                {{ $title }}
                            </h1>
                        </div>
                    </div>
                @endif
                <main class="mt-16">
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
