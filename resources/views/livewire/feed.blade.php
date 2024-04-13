<div>
    <section class="mb-12 min-h-screen space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" />
        @empty
            <div class="text-center text-slate-400">There are no questions to show.</div>
        @endforelse

        @if ($perPage < 100 && $questions->hasMorePages())
            <div x-intersect="$wire.loadMore()"></div>
        @elseif ($perPage > 10)
            <div class="text-center text-slate-400">There are no more questions to load, or you have scrolled too far.</div>
        @endif
    </section>
</div>
