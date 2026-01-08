<div>
    <section class="mb-12 min-h-screen space-y-10">
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
            <div class="text-center dark:text-slate-400 text-slate-600">There are no questions to show.</div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
