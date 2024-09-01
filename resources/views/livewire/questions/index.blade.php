<section class="divide-y sm:mt-4 divide-slate-800 sm:divide-y-0 sm:space-y-10">
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
