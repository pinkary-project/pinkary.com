<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.components.head')
    </head>
    @php
        $showDiscoverLayout = request()->routeIs('home.*') || request()->routeIs('hashtag.show');
        $showUtilityRail = request()->routeIs('bookmarks.*') || request()->routeIs('notifications.*');
        $showRightRail = $showDiscoverLayout || $showUtilityRail || request()->routeIs('profile.show') || request()->routeIs('questions.show');
        $globalSearchQuery = request()->routeIs('home.users')
            ? (string) request()->query('q', '')
            : (request()->routeIs('hashtag.show') ? '#'.request()->route('hashtag') : '');
        $recentSignups = $showRightRail
            ? \App\Models\User::query()
                ->whereNotNull('username')
                ->when(auth()->check(), fn ($query) => $query->whereKeyNot(auth()->id()))
                ->latest()
                ->limit(5)
                ->get()
            : collect();
    @endphp
    <body class="bg-slate-100 font-sans antialiased text-slate-950 dark:bg-[#060c18] dark:text-slate-50">
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="pointer-events-none fixed inset-0 -z-10 hidden overflow-hidden dark:block">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#040915_0%,#081223_100%)]"></div>
            <div class="absolute inset-x-0 top-0 h-48 bg-[radial-gradient(circle_at_top,_rgba(236,72,153,0.12),_transparent_60%)]"></div>
            <div class="absolute left-[28%] top-0 h-64 w-64 -translate-x-1/2 rounded-full bg-pink-500/8 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-24 h-72 w-72 rounded-full bg-sky-500/6 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen flex-col">
            <div class="mx-auto flex w-full max-w-7xl flex-1 px-0 pb-28 lg:grid lg:pb-0 {{ $showRightRail ? 'lg:grid-cols-[18rem_minmax(0,1fr)_20rem]' : 'lg:grid-cols-[18rem_minmax(0,1fr)]' }}">
                <aside class="lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
                    @include('layouts.navigation')
                </aside>

                <div class="min-w-0 {{ $showDiscoverLayout ? 'lg:col-start-2' : '' }}">
                    @if ($showDiscoverLayout)
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
                            class="hidden items-center gap-3 border-b border-r border-slate-200/70 bg-white/90 px-6 py-4 dark:border-slate-800/30 dark:bg-[#050c1d]/90 lg:flex"
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
                            class="sticky top-0 z-40 flex items-center gap-3 border-b border-slate-200/70 bg-white/95 px-6 py-4 backdrop-blur dark:border-slate-800/30 dark:bg-[#050c1d]/95 lg:hidden"
                        >
                            <x-heroicon-o-magnifying-glass class="size-5 text-slate-400 dark:text-slate-500" />

                            <input
                                x-model="query"
                                type="text"
                                name="q"
                                autocomplete="off"
                                placeholder="Search users or hashtags..."
                                class="w-full border-0 bg-transparent p-0 text-sm text-slate-950 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-slate-500"
                            />
                        </form>
                    @endif

                    @if (isset($title))
                        <div class="{{ $showDiscoverLayout ? 'mb-6 w-full pt-2 lg:pt-0' : 'mx-auto mb-6 w-full max-w-[44rem] pt-2 lg:pt-4' }}">
                            <h1 class="font-mona text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                                {{ $title }}
                            </h1>
                        </div>
                    @endif

                    <main class="w-full">
                        {{ $slot }}
                    </main>

                    <x-image-lightbox />
                </div>

                @if ($showRightRail)
                    <aside class="hidden lg:col-start-3 lg:block">
                        <div class="lg:sticky lg:top-0">
                            <section class="overflow-hidden border-b border-r border-slate-200/70 bg-white/80 dark:border-slate-800/30 dark:bg-[#071121]/95">
                                <div class="flex items-center justify-between border-b border-slate-200/70 px-6 py-6 dark:border-slate-800/30">
                                    <h2 class="text-[1.05rem] font-semibold text-slate-950 dark:text-white">People to follow</h2>

                                    <a
                                        href="{{ route('home.users') }}"
                                        class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
                                        wire:navigate
                                    >
                                        View all
                                    </a>
                                </div>

                                <ul class="divide-y divide-slate-200/70 dark:divide-slate-800/30">
                                    @foreach ($recentSignups as $user)
                                        <li>
                                            <a
                                                href="{{ route('profile.show', ['username' => $user->username]) }}"
                                                class="flex items-center gap-3 px-6 py-4 transition hover:bg-slate-100 dark:hover:bg-slate-900/60"
                                                wire:navigate
                                            >
                                                <img
                                                    src="{{ $user->avatar_url }}"
                                                    alt="{{ $user->username }}"
                                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-9 w-9 flex-shrink-0"
                                                />

                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-slate-950 dark:text-white">
                                                        {{ $user->name }}
                                                    </p>

                                                    <p class="truncate text-sm text-slate-400">
                                                        {{ '@'.$user->username }}
                                                    </p>
                                                </div>

                                                <span class="flex-shrink-0 text-xs text-slate-500">
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
