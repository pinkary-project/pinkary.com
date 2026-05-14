<div class="w-full text-slate-700 dark:text-slate-200">
    @if ($trendingQuestions->isEmpty())
        <section>
            <p class="my-8 text-center text-lg text-slate-500 dark:text-slate-400">There is no trending questions right now.</p>
        </section>
    @else
        <section class="min-h-screen">
            @foreach ($trendingQuestions as $question)
                <div class="border-b border-slate-200 dark:border-slate-700/50 px-6 py-6 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
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
