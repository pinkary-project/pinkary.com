<div>
    <section class="mb-12 min-h-screen space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="false" />
        @empty
            <div class="text-center text-slate-400">There are no questions to show.</div>
        @endforelse

        @if ($perPage < 100 && $questions->hasMorePages())
            <div
                x-data="{
                    observe () {
                        let observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    @this.call('loadMore')
                                }
                            })
                        }, {
                            root: null
                        })

                        observer.observe(this.$el)
                    }
                }"
                x-init="observe"
            ></div>
        @elseif ($perPage > 10)
            <div class="text-center text-slate-400">There are no more questions to load, or you have scrolled too far.</div>
        @endif
    </section>
</div>
