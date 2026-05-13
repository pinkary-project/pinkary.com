<div class="w-full text-gray-200">
    @if ($followingQuestions->isEmpty())
        <section>
            <p class="my-8 text-center text-lg text-slate-500">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </section>
    @else
        <section class="min-h-screen divide-y divide-white/5">
            @foreach ($followingQuestions as $question)
                <div class="px-6 py-6 transition hover:bg-gray-800/20">
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
