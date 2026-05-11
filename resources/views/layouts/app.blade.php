<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    <body class="font-sans antialiased text-slate-950 dark:text-slate-50">
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-slate-100 dark:bg-slate-950"></div>
            <div class="absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,_rgba(236,72,153,0.18),_transparent_58%)] dark:bg-[radial-gradient(circle_at_top,_rgba(244,114,182,0.18),_transparent_52%)]"></div>
            <div class="absolute left-[-8rem] top-24 h-56 w-56 rounded-full bg-pink-500/10 blur-3xl dark:bg-pink-500/10"></div>
            <div class="absolute right-[-4rem] top-40 h-64 w-64 rounded-full bg-sky-500/10 blur-3xl dark:bg-sky-500/10"></div>
            <div
                class="absolute inset-0 opacity-[0.14] dark:opacity-[0.08]"
                style="background-image: url({{ asset('/img/dots.svg') }}); background-position: center; background-repeat: repeat;"
            ></div>
        </div>

        <div class="relative flex min-h-screen flex-col">
            <div class="mx-auto flex w-full max-w-[82rem] flex-1 px-4 pb-28 pt-6 sm:px-6 lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-8 lg:px-8 lg:pb-12 lg:pt-8">
                <aside class="lg:sticky lg:top-8 lg:flex lg:h-[calc(100vh-4rem)] lg:flex-col lg:justify-between lg:gap-4">
                    @include('layouts.navigation')
                </aside>

                <div class="min-w-0">
                    @if (isset($title))
                        <div class="mx-auto mb-8 w-full max-w-[44rem] pt-2 lg:pt-4">
                            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                                Pinkary
                            </div>

                            <h1 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                                {{ $title }}
                            </h1>
                        </div>
                    @endif

                    <main class="mx-auto w-full max-w-[44rem]">
                        {{ $slot }}
                    </main>

                    <x-image-lightbox />
                </div>
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
