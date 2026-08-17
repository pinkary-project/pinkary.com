<div>
    <div class="border-b border-slate-200/70 bg-white/60 p-4 sm:p-6 dark:border-slate-800/30 dark:bg-[#07101f]/60">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-slate-950 dark:text-white"> #{{ $topic->name }} </span>
                </div>

                @if ($topic->description)
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $topic->description }}</p>
                @endif

                <div class="mt-3 flex items-center gap-4 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <span>{{ Number::format($topic->followers_count) }} {{ str('follower')->plural($topic->followers_count) }}</span>
                    <span>&middot;</span>
                    <span>{{ Number::format($topic->questions_count) }} {{ str('post')->plural($topic->questions_count) }}</span>
                </div>
            </div>

            @auth
                <div class="shrink-0">
                    <button
                        type="button"
                        wire:click="toggleFollow"
                        class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-xs font-semibold transition {{ $isFollowed ? 'border-pink-500 bg-pink-500 text-white hover:bg-pink-600' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038]' }}"
                    >
                        {{ $isFollowed ? __('Following') : __('Follow') }}
                    </button>
                </div>
            @endauth
        </div>

        <div class="mt-6 flex items-center gap-2 border-t border-slate-200/70 pt-4 dark:border-slate-800/30">
            <button
                type="button"
                wire:click="setSort('recent')"
                class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $sort === 'recent' ? 'bg-slate-950 text-white dark:bg-[#1a2438]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white' }}"
            >
                {{ __('Recent') }}
            </button>

            <button
                type="button"
                wire:click="setSort('trending')"
                class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $sort === 'trending' ? 'bg-slate-950 text-white dark:bg-[#1a2438]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white' }}"
            >
                {{ __('Trending') }}
            </button>
        </div>
    </div>

    @auth
        <div class="border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/30">
            <livewire:questions.create :toId="auth()->id()" :topicId="$topic->id" />
        </div>
    @endauth

    <section class="min-h-screen space-y-0">
        @forelse ($questions as $question)
            <div
                wire:key="topic-thread-{{ $question->id }}"
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
                    {{ __('No posts in this topic yet.') }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Be the first to share an update about :topic.', ['topic' => $topic->name]) }}
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
