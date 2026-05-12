<div class="mb-12 w-full dark:text-slate-200 text-slate-800">
    @if ($followingQuestions->isEmpty())
        <section>
            <p class="my-8 text-center text-lg text-slate-500">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </section>
    @else
        <section class="mb-10 min-h-screen divide-y divide-slate-800">
            @foreach ($followingQuestions as $question)
                <div class="py-3">
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
