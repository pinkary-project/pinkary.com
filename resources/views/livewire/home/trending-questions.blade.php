<div class="w-full text-gray-200">
    @if ($trendingQuestions->isEmpty())
        <section class="p-6 xl:px-8">
            <p class="text-center">There is no trending questions right now.</p>
        </section>
    @else
        <ul role="list" class="divide-y divide-white/5">
            @foreach ($trendingQuestions as $question)
                <li class="cursor-pointer hover:bg-gray-800/20">
                    <livewire:questions.show
                        :questionId="$question->id"
                        :key="'question-' . $question->id"
                        :inIndex="true"
                        :pinnable="false"
                        :trending="true"
                    />
                </li>
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$trendingQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </ul>
    @endif
</div>
