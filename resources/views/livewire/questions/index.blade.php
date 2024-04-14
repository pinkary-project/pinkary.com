<section class="mt-4 space-y-10">
    @foreach ($questions as $question)
        <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" :pinnable="$pinnable" />
    @endforeach

    {{ $this->getLoadMoreButton($questions, 'There are no more questions to load, or you have scrolled too far.') }}
</section>
