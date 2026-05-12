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
    <body class="bg-[#060c18] font-sans antialiased text-slate-950 dark:text-slate-50">
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#040915_0%,#081223_100%)]"></div>
            <div class="absolute inset-x-0 top-0 h-48 bg-[radial-gradient(circle_at_top,_rgba(236,72,153,0.12),_transparent_60%)]"></div>
            <div class="absolute left-[28%] top-0 h-64 w-64 -translate-x-1/2 rounded-full bg-pink-500/8 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-24 h-72 w-72 rounded-full bg-sky-500/6 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen flex-col">
            <div class="mx-auto flex w-full max-w-[96rem] flex-1 px-4 pb-20 pt-3 sm:px-5 lg:grid lg:px-6 lg:pb-6 lg:pt-3 {{ $showDiscoverLayout ? 'lg:grid-cols-[17.5rem_minmax(0,1fr)_19rem] lg:gap-x-3 lg:gap-y-0' : 'lg:grid-cols-[15.5rem_minmax(0,1fr)] lg:gap-5' }}">
                <aside class="{{ $showDiscoverLayout ? 'lg:row-span-2' : '' }} lg:sticky lg:top-5 lg:flex lg:h-[calc(100vh-2.5rem)] lg:flex-col lg:gap-4">
                    @include('layouts.navigation')
                </aside>

                @if ($showDiscoverLayout)
                    <div class="hidden lg:col-[2/4] lg:block">
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
                            class="flex items-center gap-3 border border-slate-800 bg-[#050c1d]/90 px-4 py-2.5"
                        >
                            <x-heroicon-o-magnifying-glass class="size-5 text-slate-500" />

                            <input
                                x-model="query"
                                type="text"
                                name="q"
                                autocomplete="off"
                                placeholder="Search for users or hashtags..."
                                class="w-full border-0 bg-transparent p-0 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-0"
                            />
                        </form>
                    </div>
                @endif

                <div class="min-w-0">
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
                    <aside class="hidden lg:block">
                        <div class="lg:sticky lg:top-3">
                            <section class="overflow-hidden border border-slate-800 border-t-0 bg-[#071121]/95">
                                <div class="flex items-center justify-between border-b border-slate-800 px-3 py-3">
                                    <h2 class="text-[1.05rem] font-semibold text-white">People to follow</h2>

                                    <a
                                        href="{{ route('home.users') }}"
                                        class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
                                        wire:navigate
                                    >
                                        View all
                                    </a>
                                </div>

                                <ul class="divide-y divide-slate-800">
                                    @foreach ($recentSignups as $user)
                                        <li>
                                            <a
                                                href="{{ route('profile.show', ['username' => $user->username]) }}"
                                                class="flex items-center gap-3 px-3 py-3 transition hover:bg-slate-900/60"
                                                wire:navigate
                                            >
                                                <img
                                                    src="{{ $user->avatar_url }}"
                                                    alt="{{ $user->username }}"
                                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-9 w-9 flex-shrink-0"
                                                />

                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-white">
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
