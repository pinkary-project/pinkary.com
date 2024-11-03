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
        <div class="flex min-h-screen" x-data="{ sidebar: false }">
            <div class="md:hidden">
                <button
                    class="fixed z-40 top-4 left-4 p-2 bg-white dark:bg-slate-900 rounded-lg shadow-md"
                    x-on:click="sidebar = !sidebar"
                >
                    <x-icons.bars class="size-6"/>
                </button>
            </div>

            <aside
                x-show="sidebar"
                x-cloak
                x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                x-on:click.away="sidebar = false"
                class="fixed z-50 top-0 left-0 w-64 h-full bg-white dark:bg-slate-900 shadow-lg"
            >
                @include('layouts.navigation', ['mobile' => true])
            </aside>

            <header class="hidden md:block w-auto lg:w-1/5 border-r dark:border-slate-800 border-slate-300 min-h-screen">
                <div class="sticky top-0">
                    @include('layouts.navigation')
                </div>
            </header>
            <div class="flex-grow">
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
                    @persist('footer')
                        <x-back-to-top :offset="300" />
                        <x-footer />
                        <x-image-lightbox />
                    @endpersist
                </main>
            </div>
            <aside class="hidden lg:block w-1/3 border-l dark:border-slate-800 border-slate-300">
                <div class="sticky top-0">
                    @isset($aside)
                        {{ $aside }}
                    @else
                        <livewire:home.aside />
                    @endisset
                </div>
            </aside>
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
