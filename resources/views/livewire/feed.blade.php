<div>
    <section class="min-h-screen space-y-0">
        @forelse ($questions as $question)
            <div
                wire:key="thread-{{ $question->id }}"
                class="border-b border-slate-200 px-2 py-2 transition hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-[#0a1325]"
            >
                @if ($hashtag !== null && $hashtag !== '')
                    <livewire:questions.show
                        :questionId="$question->id"
                        :key="'question-'.$question->id"
                        :inIndex="true"
                    />
                @else
                    <x-thread
                        :rootId="$question->root?->id"
                        :grandParentId="$question->parent?->parent_id"
                        :parentId="$question->parent?->id"
                        :questionId="$question->id"
                        :username="$question->root?->to->username"
                    />
                @endif
            </div>
        @empty
            <div class="py-5 text-center">
                <p class="text-lg font-medium text-slate-950 dark:text-white">There are no questions to show.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Try switching to another feed view or come back once new posts are published.
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
