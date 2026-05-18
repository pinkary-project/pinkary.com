<section class="mb-12">
    @if ($pinnedQuestion)
        <div class="border-b border-slate-200 px-2 py-2 dark:border-slate-700/50">
            <livewire:questions.show
                :questionId="$pinnedQuestion->id"
                :key="'pinned-question-' . $pinnedQuestion->id"
                :inIndex="true"
                :pinnable="true"
            />
        </div>
    @endif

    @foreach ($questions as $question)
        <div class="border-b border-slate-200 px-2 py-2 last:border-b-0 dark:border-slate-700/50">
            <x-thread
                :rootId="$question->showRoot ? $question->root_id : null"
                :grandParentId="$question->parent?->parent_id"
                :parentId="$question->showParent ? $question->parent_id : null"
                :questionId="$question->id"
                :username="$user->username"
            />
        </div>
    @endforeach

    <div class="px-4 py-4">
        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </div>
</section>
