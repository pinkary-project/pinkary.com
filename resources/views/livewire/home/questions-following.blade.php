<div>
    @if ($followingQuestions->isEmpty())
        <div class="p-6 xl:px-8">
            <p class="text-center dark:text-slate-200 text-slate-600">
                No questions match your activity on Pinkary.
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
