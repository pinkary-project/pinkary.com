<div>
    <div class="border-b border-slate-200/70 bg-white/50 p-4 dark:border-slate-800/30 dark:bg-[#07101f]/50">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="absolute top-3 left-3 size-4 text-slate-400 dark:text-slate-500" />
            <input
                wire:model.live.debounce.250ms="search"
                type="text"
                placeholder="Search topics..."
                class="w-full rounded-md border border-slate-200/70 bg-white py-2 pr-3 pl-9 text-sm text-slate-900 placeholder:text-slate-400 focus:border-pink-500 focus:ring-0 focus:outline-none dark:border-slate-800/40 dark:bg-[#10182b] dark:text-white dark:placeholder:text-slate-500"
            />
        </div>
    </div>

    <div class="divide-y divide-slate-200/70 dark:divide-slate-800/30">
        @forelse ($topics as $topic)
            @php
                $isFollowed = in_array($topic->id, $followedTopicIds, true);
            @endphp
            <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                <a
                    href="{{ route('topics.show', ['topic' => $topic->slug]) }}"
                    class="min-w-0 flex-1 pr-4"
                    wire:navigate
                >
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-slate-950 hover:text-pink-600 dark:text-white dark:hover:text-pink-400">
                            #{{ $topic->name }}
                        </span>
                    </div>

                    @if ($topic->description)
                        <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $topic->description }}
                        </p>
                    @endif

                    <div class="mt-2 flex items-center gap-4 text-xs text-slate-400">
                        <span>{{ Number::format($topic->followers_count) }} {{ str('follower')->plural($topic->followers_count) }}</span>
                        <span>&middot;</span>
                        <span>{{ Number::format($topic->questions_count) }} {{ str('post')->plural($topic->questions_count) }}</span>
                    </div>
                </a>

                @auth
                    <div>
                        <button
                            type="button"
                            wire:click="toggleFollow({{ $topic->id }})"
                            class="inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-xs font-semibold transition {{ $isFollowed ? 'border-pink-500 bg-pink-500 text-white hover:bg-pink-600' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038]' }}"
                        >
                            {{ $isFollowed ? __('Following') : __('Follow') }}
                        </button>
                    </div>
                @endauth
            </div>
        @empty
            <div class="py-12 text-center">
                <p class="text-base font-medium text-slate-950 dark:text-white">{{ __('No topics found.') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Try searching with different keywords.') }}
                </p>
            </div>
        @endforelse
    </div>
</div>
