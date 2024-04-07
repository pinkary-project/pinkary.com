<div>
    <section class="mb-12 min-h-screen space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" />
        @empty
            <div class="text-center text-slate-400">There are no questions to show.</div>
        @endforelse
    </section>

    @if ($questions->hasMorePages())
        <livewire:feed :page="$this->page + 1" lazy />
    @else
        <div class="text-center text-slate-400 mb-12">
            There are no more questions to load, or you have scrolled too far.
        </div>
    @endif
</div>
