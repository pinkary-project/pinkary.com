<div class="w-full text-slate-700 dark:text-slate-200">
    @if ($followingQuestions->isEmpty())
        <section>
            <p class="my-8 text-center text-lg text-slate-500 dark:text-slate-400">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </section>
    @else
        <section class="min-h-screen">
            @foreach ($followingQuestions as $question)
                <div class="px-6 py-6 transition hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                    <x-thread
                        :rootId="$question->showRoot ? $question->root_id : null"
                        :grandParentId="$question->parent?->parent_id"
                        :parentId="$question->showParent ? $question->parent_id : null"
                        :questionId="$question->id"
                        :username="$question->root?->to->username"
                    />
                </div>
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$followingQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </section>
    @endif
</div>
