<div class="space-y-6 p-4 sm:p-6">
    @auth
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('Your Custom Feeds') }}</h3>
            <a
                href="{{ route('feeds.create') }}"
                class="inline-flex items-center gap-1.5 rounded-md border border-pink-500 bg-pink-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-pink-600"
                wire:navigate
            >
                <x-heroicon-m-plus class="size-4" />
                <span>{{ __('Create Feed') }}</span>
            </a>
        </div>

        @if ($myFeeds->isNotEmpty())
            <div class="divide-y divide-slate-200/70 rounded-md border border-slate-200/70 bg-white/50 dark:divide-slate-800/30 dark:border-slate-800/30 dark:bg-[#07101f]/50">
                @foreach ($myFeeds as $feed)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                        <a
                            href="{{ route('feeds.show', ['feed' => $feed->id]) }}"
                            class="min-w-0 flex-1 pr-4"
                            wire:navigate
                        >
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-950 hover:text-pink-600 dark:text-white dark:hover:text-pink-400">
                                    {{ $feed->name }}
                                </span>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wider {{ $feed->isPublic() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $feed->visibility->value }}
                                </span>
                            </div>

                            @if ($feed->description)
                                <p class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $feed->description }}
                                </p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                <span>{{ $feed->topics->count() }} {{ str('topic')->plural($feed->topics->count()) }}</span>
                                <span>&middot;</span>
                                <span>{{ $feed->people->count() }} {{ str('person')->plural($feed->people->count()) }}</span>
                                <span>&middot;</span>
                                <span>{{ Number::format($feed->followers_count) }} {{ str('follower')->plural($feed->followers_count) }}</span>
                            </div>
                        </a>

                        <div class="flex shrink-0 items-center gap-2">
                            <a
                                href="{{ route('feeds.edit', ['feed' => $feed->id]) }}"
                                class="rounded-md border border-slate-200 bg-white p-1.5 text-slate-500 hover:text-slate-950 dark:border-slate-800 dark:bg-[#10182b] dark:text-slate-400 dark:hover:text-white"
                                title="Edit"
                                wire:navigate
                            >
                                <x-heroicon-m-pencil class="size-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-md border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                <p class="text-sm font-medium text-slate-950 dark:text-white">
                    {{ __('You have not created any feeds yet.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Create custom feeds combining specific topics and people you care about.') }}
                </p>
            </div>
        @endif

        @if ($followedFeeds->isNotEmpty())
            <div class="pt-4">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('Feeds You Follow') }}</h3>
                <div class="mt-3 divide-y divide-slate-200/70 rounded-md border border-slate-200/70 bg-white/50 dark:divide-slate-800/30 dark:border-slate-800/30 dark:bg-[#07101f]/50">
                    @foreach ($followedFeeds as $feed)
                        <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                            <a
                                href="{{ route('feeds.show', ['feed' => $feed->id]) }}"
                                class="min-w-0 flex-1 pr-4"
                                wire:navigate
                            >
                                <p class="font-semibold text-slate-950 hover:text-pink-600 dark:text-white dark:hover:text-pink-400">
                                    {{ $feed->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    by {{ '@'.$feed->user->username }} &middot; {{ Number::format($feed->followers_count) }} {{ str('follower')->plural($feed->followers_count) }}
                                </p>
                            </a>

                            <button
                                type="button"
                                wire:click="toggleFollow({{ $feed->id }})"
                                class="inline-flex items-center justify-center rounded-md border border-pink-500 bg-pink-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-pink-600"
                            >
                                {{ __('Following') }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endauth

    <div class="pt-4">
        <h3 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('Discover Public Feeds') }}</h3>
        @if ($discoverFeeds->isNotEmpty())
            <div class="mt-3 divide-y divide-slate-200/70 rounded-md border border-slate-200/70 bg-white/50 dark:divide-slate-800/30 dark:border-slate-800/30 dark:bg-[#07101f]/50">
                @foreach ($discoverFeeds as $feed)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                        <a
                            href="{{ route('feeds.show', ['feed' => $feed->id]) }}"
                            class="min-w-0 flex-1 pr-4"
                            wire:navigate
                        >
                            <p class="font-semibold text-slate-950 hover:text-pink-600 dark:text-white dark:hover:text-pink-400">
                                {{ $feed->name }}
                            </p>
                            @if ($feed->description)
                                <p class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $feed->description }}
                                </p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400">
                                by {{ '@'.$feed->user->username }} &middot; {{ Number::format($feed->followers_count) }} {{ str('follower')->plural($feed->followers_count) }}
                            </p>
                        </a>

                        @auth
                            <button
                                type="button"
                                wire:click="toggleFollow({{ $feed->id }})"
                                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038]"
                            >
                                {{ __('Follow') }}
                            </button>
                        @endauth
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-3 rounded-md border border-slate-200 p-8 text-center dark:border-slate-800">
                <p class="text-sm font-medium text-slate-950 dark:text-white">
                    {{ __('No public feeds available to discover yet.') }}
                </p>
            </div>
        @endif
    </div>
</div>
