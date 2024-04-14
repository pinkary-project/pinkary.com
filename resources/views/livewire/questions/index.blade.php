<section class="mt-4 space-y-10">
    @foreach ($questions as $question)
        <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" :pinnable="$pinnable" />
    @endforeach

    @if ($perPage < 100 && $questions->hasMorePages())
        <div x-intersect="$wire.loadMore()"></div>
    @elseif ($perPage > 10)
        <div class="text-center text-slate-400">There are no more questions to load, or you have scrolled too far.</div>
    @endif
</section>
