<div class="w-full text-gray-200">
    @if ($trendingQuestions->isEmpty())
        <section>
            <p class="my-8 text-center text-lg text-slate-500">There is no trending questions right now.</p>
        </section>
    @else
        <section class="min-h-screen divide-y divide-white/5">
            @foreach ($trendingQuestions as $question)
                <div class="px-6 py-6 transition hover:bg-gray-800/20">
                    <livewire:questions.show
                        :questionId="$question->id"
                        :key="'question-' . $question->id"
                        :inIndex="true"
                        :pinnable="false"
                        :trending="true"
                    />
                </div>
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$trendingQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </section>
    @endif
</div>
