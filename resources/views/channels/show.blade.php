<x-app-layout>
    <section
        data-current-channel-id="{{ $channel->id }}"
        data-current-channel-name="{{ $channel->name }}"
        class="border-x border-b border-slate-200 bg-white/80 dark:border-slate-700/50 dark:bg-[#07101f]/95"
    >
        <div class="sticky -top-1 z-30 flex items-center justify-between border-b border-slate-200/70 bg-white px-4 py-3 sm:bg-white/90 sm:px-6 sm:py-4 sm:backdrop-blur dark:border-slate-800/30 dark:bg-[#07101f] dark:sm:bg-[#07101f]/95">
            <div>
                <p class="flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400">
                    <x-heroicon-o-tag class="size-3.5" />
                    <span>{{ __('Channel') }}</span>
                </p>
                <h2 class="mt-0.5 text-[2rem] font-semibold tracking-tight text-slate-950 dark:text-white">
                    {{ $channel->name }}
                </h2>
                @if ($channel->description)
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $channel->description }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-0">
            @auth
                <div class="hidden border-b border-slate-200/70 px-4 py-4 sm:block dark:border-slate-800/30">
                    <livewire:questions.create
                        :toId="auth()->id()"
                        :channelId="$channel->id"
                        key="channel-create-post-{{ $channel->id }}"
                    />
                </div>
            @endauth

            <div class="min-h-screen">
                <livewire:channels.show :channel="$channel" />
            </div>
        </div>
    </section>
</x-app-layout>
