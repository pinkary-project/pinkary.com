<div class="w-full mb-12 text-slate-200">
    @if ($forYouQuestions->isEmpty())
        <section class="rounded-lg">
            <p class="px-5 my-8 text-lg text-center sm:px-0 text-slate-500">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </section>
    @else
        <section class="min-h-screen divide-y sm:mb-12 divide-slate-800 sm:divide-y-0 sm:space-y-10">
            @foreach ($forYouQuestions as $question)
                <livewire:questions.show
                    :questionId="$question->id"
                    :key="'question-' . $question->id"
                    :inIndex="true"
                    :pinnable="false"
                />
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$forYouQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </section>
    @endif
</div>
