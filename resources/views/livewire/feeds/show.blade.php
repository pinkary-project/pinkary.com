<div>
    <div class="border-b border-slate-200/70 bg-white/60 p-4 sm:p-6 dark:border-slate-800/30 dark:bg-[#07101f]/60">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-bold text-slate-950 dark:text-white">{{ $feed->name }}</h2>

                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $feed->isPublic() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ $feed->visibility->value }}
                    </span>
                </div>

                @if ($feed->description)
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $feed->description }}</p>
                @endif

                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ __('Curated by') }}</span>
                    <a
                        href="{{ route('profile.show', ['username' => $feed->user->username]) }}"
                        class="font-semibold text-slate-800 hover:text-pink-600 dark:text-slate-200 dark:hover:text-pink-400"
                        wire:navigate
                    >
                        {{ '@'.$feed->user->username }}
                    </a>
                    <span>&middot;</span>
                    <span>{{ Number::format($feed->followers_count) }} {{ str('follower')->plural($feed->followers_count) }}</span>
                </div>

                @if ($feed->topics->isNotEmpty() || $feed->people->isNotEmpty())
                    <div class="mt-4 flex flex-wrap items-center gap-1.5">
                        @foreach ($feed->topics as $topic)
                            <a
                                href="{{ route('topics.show', ['topic' => $topic->slug]) }}"
                                class="inline-flex items-center gap-1 rounded-full bg-pink-50 px-2.5 py-0.5 text-xs font-medium text-pink-700 transition hover:bg-pink-100 dark:bg-pink-950/40 dark:text-pink-300 dark:hover:bg-pink-900/50"
                                wire:navigate
                            >
                                <span>#{{ $topic->name }}</span>
                            </a>
                        @endforeach

                        @foreach ($feed->people as $person)
                            <a
                                href="{{ route('profile.show', ['username' => $person->username]) }}"
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-[#11192b] dark:text-slate-300 dark:hover:bg-[#16203a]"
                                wire:navigate
                            >
                                <img
                                    src="{{ $person->avatar_url }}"
                                    alt="{{ $person->username }}"
                                    class="size-3.5 rounded-full"
                                />
                                <span>{{ '@'.$person->username }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-2">
                @if (auth()->id() === $feed->user_id)
                    <a
                        href="{{ route('feeds.edit', ['feed' => $feed->id]) }}"
                        class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038]"
                        wire:navigate
                    >
                        <x-heroicon-m-pencil class="size-3.5" />
                        <span>{{ __('Edit Feed') }}</span>
                    </a>
                @elseif (auth()->check() && $feed->isPublic())
                    <button
                        type="button"
                        wire:click="toggleFollow"
                        class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-xs font-semibold transition {{ $isFollowed ? 'border-pink-500 bg-pink-500 text-white hover:bg-pink-600' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038]' }}"
                    >
                        {{ $isFollowed ? __('Following') : __('Follow') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <section class="min-h-screen space-y-0">
        @forelse ($questions as $question)
            <div
                wire:key="custom-feed-thread-{{ $question->id }}"
                class="border-b border-slate-200 px-2 py-2 transition hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-[#0a1325]"
            >
                <x-thread
                    :rootId="$question->root?->id"
                    :grandParentId="$question->parent?->parent_id"
                    :parentId="$question->parent?->id"
                    :questionId="$question->id"
                    :username="$question->root?->to->username"
                />
            </div>
        @empty
            <div class="py-12 text-center">
                <p class="text-base font-medium text-slate-950 dark:text-white">
                    {{ __('No posts in this feed yet.') }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Posts matching the included topics or people will appear here as they are published.') }}
                </p>
            </div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
