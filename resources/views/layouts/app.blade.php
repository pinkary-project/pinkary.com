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
    <body class="dark bg-gray-900 font-sans antialiased text-gray-200">
        @persist('flash-messages')
            <livewire:flash-messages.show />
        @endpersist
        <div class="relative flex min-h-screen flex-col">
            <div class="mx-auto flex w-full max-w-7xl flex-1 px-0 pb-20 lg:grid lg:pb-0 {{ $showDiscoverLayout ? 'lg:grid-cols-[18rem_minmax(0,1fr)_20rem]' : 'lg:grid-cols-[18rem_minmax(0,1fr)]' }}">
                <aside class="{{ $showDiscoverLayout ? 'lg:row-span-2' : '' }} lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
                    @include('layouts.navigation')
                </aside>

                @if ($showDiscoverLayout)
                    <div class="hidden lg:col-start-2 lg:row-start-1 lg:-mb-px lg:block">
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
                            class="flex items-center gap-3 border-b border-r border-white/5 bg-black/10 px-6 py-4"
                        >
                            <x-heroicon-o-magnifying-glass class="size-5 text-slate-500" />

                            <input
                                x-model="query"
                                type="text"
                                name="q"
                                autocomplete="off"
                                placeholder="Search for users or hashtags..."
                                class="w-full border-0 bg-transparent p-0 text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0"
                            />
                        </form>
                    </div>
                @endif

                <div class="min-w-0 {{ $showDiscoverLayout ? 'lg:col-start-2 lg:row-start-2' : 'px-4 py-6 lg:px-8' }}">
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
                    <aside class="hidden lg:col-start-3 lg:row-start-2 lg:block">
                        <div class="lg:sticky lg:top-0">
                            <section class="overflow-hidden border-b border-r border-white/5 bg-black/10">
                                <div class="flex items-center justify-between border-b border-white/5 px-6 py-6">
                                    <h2 class="text-[1.05rem] font-semibold text-white">People to follow</h2>

                                    <a
                                        href="{{ route('home.users') }}"
                                        class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
                                        wire:navigate
                                    >
                                        View all
                                    </a>
                                </div>

                                <ul class="divide-y divide-white/5">
                                    @foreach ($recentSignups as $user)
                                        <li>
                                            <a
                                                href="{{ route('profile.show', ['username' => $user->username]) }}"
                                                class="flex items-center gap-3 px-6 py-4 transition hover:bg-gray-800/20"
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
