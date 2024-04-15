<div class="mx-auto">
    <div class="questions">
        @foreach ($questions as $question)
            <x-questions.show
                :key="'question-' . $question->id"
                :question="$question"
                :pinnable="$pinnable"
                :inIndex="true"
            />
        @endforeach
    </div>

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$questions"
        message="There are no more questions to load, or you have scrolled too far."
    />
</div>
