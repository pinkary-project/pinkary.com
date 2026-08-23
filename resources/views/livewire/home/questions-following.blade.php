<div class="w-full text-slate-700 dark:text-slate-200">
    <section class="min-h-screen space-y-0">
        @forelse ($followingQuestions as $question)
            <div
                data-question-item
                class="border-b border-slate-200 px-2 py-2 transition hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-[#0a1325]"
            >
                <x-thread
                    :rootId="$question->showRoot ? $question->root_id : null"
                    :grandParentId="$question->parent?->parent_id"
                    :parentId="$question->showParent ? $question->parent_id : null"
                    :questionId="$question->id"
                    :username="$question->root?->to->username"
                />
            </div>
        @empty
            <div class="py-8 text-center">
                <p class="text-lg font-medium text-slate-950 dark:text-white">
                    {{ __('Your following feed is empty.') }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Follow more people or check back once they publish new questions.') }}
                </p>
            </div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$followingQuestions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
