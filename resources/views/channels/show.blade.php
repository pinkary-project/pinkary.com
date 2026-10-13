<x-app-layout>
    <section
        data-current-channel-id="{{ $channel->id }}"
        data-current-channel-name="{{ $channel->name }}"
        class="flex flex-1 flex-col border-x border-b border-slate-200 bg-white/80 dark:border-slate-700/50 dark:bg-[#07101f]/95"
    >
        <div
            class="sticky -top-1 z-30 flex items-center justify-between border-b border-slate-200/70 bg-white px-4 py-3.5 sm:bg-white/90 sm:px-6 sm:py-4 sm:backdrop-blur dark:border-slate-800/30 dark:bg-[#07101f] dark:sm:bg-[#07101f]/95"
            x-data="{
                count: {{ $channel->questions_count }},
                get label() {
                    if (this.count === 1) return '1 post';
                    return `${this.count} posts`;
                }
            }"
            x-on:channel-count-updated.window="if ($event.detail?.channelId === {{ $channel->id }}) count = $event.detail.count"
        >
            <div class="flex items-center gap-2.5">
                <x-heroicon-o-tag class="size-6 shrink-0 text-pink-500" />
                <h1 class="font-mona text-2xl font-bold tracking-tight text-slate-950 sm:text-[1.75rem] dark:text-white">
                    {{ $channel->name }}
                </h1>
                <span class="text-xs text-slate-400 dark:text-slate-500" aria-hidden="true">&bull;</span>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400" x-text="label">
                    {{ trans_choice('{0} 0 posts|{1} 1 post|[2,*] :count posts', $channel->questions_count) }}
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col space-y-0">
            @auth
                <div class="hidden border-b border-slate-200/70 px-4 py-4 sm:block dark:border-slate-800/30">
                    <livewire:questions.create
                        :to-id="auth()->id()"
                        :channel-id="$channel->id"
                        key="channel-create-post-{{ $channel->id }}"
                    />
                </div>
            @endauth

            <div class="flex-1">
                <livewire:channels.show :channel="$channel" />
            </div>
        </div>
    </section>
</x-app-layout>
