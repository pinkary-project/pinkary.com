<section class="mt-4 space-y-10">
    @if ($pinnedQuestion)
        <livewire:questions.show
            :questionId="$pinnedQuestion->id"
            :key="'pinned-question-' . $pinnedQuestion->id"
            :inIndex="true"
            :pinnable="true"
        />
    @endif

    @foreach ($questions as $question)
        <x-thread
            :rootId="$question->showRoot ? $question->root_id : null"
            :grandParentId="$question->parent?->parent_id"
            :parentId="$question->showParent ? $question->parent_id : null"
            :questionId="$question->id"
            :username="$user->username"
        />
    @endforeach

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$questions"
        message="There are no more questions to load, or you have scrolled too far."
    />
</section>
