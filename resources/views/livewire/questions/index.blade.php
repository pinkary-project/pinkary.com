<section class="mt-4 space-y-10">
    @foreach ($questions as $question)
        <livewire:questions.show
            :questionId="$question->id"
            :key="'question-' . $question->id"
            :inIndex="true"
            :pinnable="$pinnable"
        />
    @endforeach

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$questions"
        message="There are no more questions to load, or you have scrolled too far."
    />
</section>
