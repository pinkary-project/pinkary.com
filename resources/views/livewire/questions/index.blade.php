<section class="mt-4 space-y-10">
    @if ($pinnedQuestion)
        <livewire:questions.show
            :questionId="$pinnedQuestion->id"
            :key="'question-' . $pinnedQuestion->id"
            :inIndex="true"
            :pinnable="true"
        />
    @endif

    @foreach ($questions as $question)
        <livewire:questions.show
            :questionId="$question->id"
            :key="'question-' . $question->id"
            :inIndex="true"
            :pinnable="false"
        />
    @endforeach

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$questions"
        message="There are no more questions to load, or you have scrolled too far."
    />
</section>
