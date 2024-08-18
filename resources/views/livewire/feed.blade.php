<div>
    <section id="feed" x-merge="append" class="mb-12 min-h-screen space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show
                :questionId="$question->id"
                :key="'question-' . $question->id"
                :inIndex="true"
            />
        @empty
            <div class="text-center text-slate-400">There are no questions to show.</div>
        @endforelse
    </section>
    @if($questions->hasMorePages())
        <div
            id="pagination"
            x-intersect.margin.600px="$ajax('{{ $questions->nextPageUrl() }}', { target: 'feed pagination', headers: {'X-Requested-With': 'XMLHttpRequest'} })"
        ></div>
    @endif
</div>
