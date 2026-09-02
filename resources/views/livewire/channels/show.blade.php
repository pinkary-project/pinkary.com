<div>
    <section class="min-h-screen space-y-0">
        @forelse ($questions as $question)
            <div
                wire:key="channel-thread-{{ $question->id }}"
                class="border-b border-slate-200 px-2 py-2 transition hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-[#0a1325]"
            >
                <livewire:questions.show
                    :questionId="$question->id"
                    :key="'channel-q-'.$question->id"
                    :inIndex="true"
                />
            </div>
        @empty
            <div class="py-12 text-center">
                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-600" />
                <p class="mt-2 text-lg font-medium text-slate-950 dark:text-white">
                    {{ __('No posts in this channel yet') }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Be the first to share an update or start a conversation.') }}
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
