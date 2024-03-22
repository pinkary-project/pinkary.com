<section class="mt-4 space-y-10">
    @foreach ($questions as $question)
        <livewire:questions.show
            :questionId="$question->id"
            :key="'question-' . $question->id"
            :inIndex="true"
            :pinnable="$pinnable"
        />
    @endforeach

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
