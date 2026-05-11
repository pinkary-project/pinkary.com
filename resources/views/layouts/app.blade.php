<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    @php
        $showDiscoverLayout = request()->routeIs('home.*') || request()->routeIs('hashtag.show');
        $globalSearchQuery = request()->routeIs('home.users')
            ? (string) request()->query('q', '')
            : (request()->routeIs('hashtag.show') ? '#'.request()->route('hashtag') : '');
        $recentSignups = $showDiscoverLayout
            ? \App\Models\User::query()
                ->whereNotNull('username')
                ->when(auth()->check(), fn ($query) => $query->whereKeyNot(auth()->id()))
                ->latest()
                ->limit(5)
                ->get()
            : collect();
    @endphp
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
            <div class="mx-auto flex w-full max-w-[88rem] flex-1 px-4 pb-28 pt-6 sm:px-6 lg:grid lg:px-8 lg:pb-12 lg:pt-8 {{ $showDiscoverLayout ? 'lg:grid-cols-[15rem_minmax(0,1fr)_19rem] lg:gap-6' : 'lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-8' }}">
                <aside class="lg:sticky lg:top-8 lg:flex lg:h-[calc(100vh-4rem)] lg:flex-col lg:justify-between lg:gap-4">
                    @include('layouts.navigation')
                </aside>

                <div class="min-w-0">
                    @if ($showDiscoverLayout)
                        <div class="mb-6 hidden lg:block">
                            <form
                                x-data="{ query: @js($globalSearchQuery) }"
                                x-on:submit.prevent="
                                    const value = query.trim();

                                    if (! value) {
                                        return;
                                    }

                                    if (value.startsWith('#')) {
                                        window.location.assign('{{ url('/hashtag') }}/' + encodeURIComponent(value.replace(/^#/, '')));
                                        return;
                                    }

                                    const params = new URLSearchParams({ q: value });

                                    window.location.assign('{{ route('home.users') }}?' + params.toString());
                                "
                                class="flex items-center gap-3 rounded-[1.75rem] border border-white/70 bg-white/85 px-5 py-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/20"
                            >
                                <x-heroicon-o-magnifying-glass class="size-5 text-slate-400 dark:text-slate-500" />

                                <input
                                    x-model="query"
                                    type="text"
                                    name="q"
                                    autocomplete="off"
                                    placeholder="Search for users or hashtags..."
                                    class="w-full border-0 bg-transparent p-0 text-sm text-slate-950 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-slate-500"
                                />
                            </form>
                        </div>
                    @endif

                    @if (isset($title))
                        <div class="{{ $showDiscoverLayout ? 'mb-8 w-full pt-2 lg:pt-0' : 'mx-auto mb-8 w-full max-w-[44rem] pt-2 lg:pt-4' }}">
                            <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                                Pinkary
                            </div>

                            <h1 class="mt-4 font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                                {{ $title }}
                            </h1>
                        </div>
                    @endif

                    <main class="{{ $showDiscoverLayout ? 'w-full' : 'mx-auto w-full max-w-[44rem]' }}">
                        {{ $slot }}
                    </main>

                    <x-image-lightbox />
                </div>

                @if ($showDiscoverLayout)
                    <aside class="hidden lg:block lg:pt-[5.5rem]">
                        <div class="lg:sticky lg:top-8">
                            <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/20">
                                <div class="flex items-center justify-between border-b border-slate-200/70 px-5 py-4 dark:border-slate-800/70">
                                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent signups</h2>

                                    <a
                                        href="{{ route('home.users') }}"
                                        class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
                                        wire:navigate
                                    >
                                        View all
                                    </a>
                                </div>

                                <ul class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                                    @foreach ($recentSignups as $user)
                                        <li>
                                            <a
                                                href="{{ route('profile.show', ['username' => $user->username]) }}"
                                                class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50/70 dark:hover:bg-slate-900/70"
                                                wire:navigate
                                            >
                                                <img
                                                    src="{{ $user->avatar_url }}"
                                                    alt="{{ $user->username }}"
                                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0"
                                                />

                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-slate-950 dark:text-white">
                                                        {{ $user->name }}
                                                    </p>

                                                    <p class="truncate text-sm text-slate-500 dark:text-slate-400">
                                                        {{ '@'.$user->username }}
                                                    </p>
                                                </div>

                                                <span class="flex-shrink-0 text-xs text-slate-400 dark:text-slate-500">
                                                    {{ $user->created_at->diffForHumans(short: true) }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        </div>
                    </aside>
                @endif
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
