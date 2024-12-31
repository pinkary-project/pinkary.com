<div>
    @if ($trendingQuestions->isEmpty())
        <div class="p-6 xl:px-8">
            <p class="text-center dark:text-slate-200 text-slate-600">
                There is no trending questions right now.
            </p>
        </div>
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
