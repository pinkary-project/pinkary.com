<div class="mb-12 w-full text-slate-200">
    @if ($forYouQuestions->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($forYouQuestions as $question)
                    <li>
                        <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" :pinnable="false" />
                    </li>
                @endforeach

                @if ($perPage < 100 && $forYouQuestions->hasMorePages())
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
            </ul>
        </section>
    @endif
</div>
