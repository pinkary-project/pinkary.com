<div>
    @if ($followingQuestions->isEmpty())
        <div class="p-6 xl:px-8">
            <p class="text-center text-lg text-slate-500">
                We haven't found any questions that may interest you based on the activity you've done on Pinkary.
            </p>
        </div>
    @else
        <ul role="list" class="divide-y divide-white/5">
            @foreach ($followingQuestions as $question)
                <li class="cursor-pointer hover:bg-gray-800/20">
                    <x-thread
                        :rootId="$question->showRoot ? $question->root_id : null"
                        :grandParentId="$question->parent?->parent_id"
                        :parentId="$question->showParent ? $question->parent_id : null"
                        :questionId="$question->id"
                        :username="$question->root?->to->username"
                    />
                </li>
            @endforeach

            <x-load-more-button
                :perPage="$perPage"
                :paginator="$followingQuestions"
                message="There are no more questions to load, or you have scrolled too far."
            />
        </ul>
    @endif
</div>
