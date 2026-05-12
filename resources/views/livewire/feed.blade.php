<div>
    <section class="min-h-screen space-y-0 divide-y divide-slate-800/40">
        @forelse ($questions as $question)
            <div wire:key="thread-{{ $question->id }}" class="py-2">
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
            <div class="py-5 text-center">
                <p class="text-lg font-medium text-white">There are no questions to show.</p>
                <p class="mt-2 text-sm text-slate-400">Try switching to another feed view or come back once new posts are published.</p>
            </div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
