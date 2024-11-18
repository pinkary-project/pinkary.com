<ul role="list" class="divide-y divide-white/5">
    @forelse ($questions as $question)
        <li class="cursor-pointer hover:bg-gray-800/20">
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
        </li>
    @empty
        <div class="p-6 xl:px-8">
            <p class="text-center dark:text-slate-200 text-slate-600">
                There are no questions to show.
            </p>
        </div>
    @endforelse

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$questions"
        message="There are no more questions to load, or you have scrolled too far."
    />
</ul>
