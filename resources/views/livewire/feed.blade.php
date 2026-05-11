<div>
    <section class="mb-6 min-h-screen space-y-6 sm:space-y-8">
        @forelse ($questions as $question)
            <div wire:key="thread-{{ $question->id }}">
                @if($hashtag !== null && $hashtag !== '')
                    <livewire:questions.show
                        :questionId="$question->id"
                        :key="'question-' . $question->id"
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
            <div class="rounded-[1.75rem] border border-dashed border-slate-300/80 bg-slate-50/70 p-8 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
                <p class="text-lg font-medium text-slate-950 dark:text-white">There are no questions to show.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try switching to another feed view or come back once new posts are published.</p>
            </div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
