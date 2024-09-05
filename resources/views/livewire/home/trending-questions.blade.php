<div class="w-full mb-12 text-slate-200">
    @if ($trendingQuestions->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-lg text-center text-slate-500">There is no trending questions right now.</p>
        </section>
    @else
        <section class="min-h-screen divide-y sm:mb-12 divide-slate-800 sm:divide-y-0 sm:space-y-10">
            @foreach ($trendingQuestions as $question)
                <livewire:questions.show
                    :questionId="$question->id"
                    :key="'question-' . $question->id"
                    :inIndex="true"
                    :pinnable="false"
                    :InTrending="true"
                />
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$trendingQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </section>
    @endif
</div>
