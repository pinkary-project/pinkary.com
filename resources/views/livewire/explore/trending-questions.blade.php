<div class="mb-12 w-full text-slate-200">
    @if ($trendingQuestions->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">There is no trending questions right now</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($trendingQuestions as $question)
                    <li>
                        <livewire:questions.show
                            :questionId="$question->id"
                            :key="'question-' . $question->id"
                            :inIndex="true"
                            :pinnable="false"
                            :trending="true"
                        />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
