<div class="w-full text-slate-700 dark:text-slate-200">
    <section class="min-h-screen space-y-0">
        @forelse ($trendingQuestions as $question)
            <div class="border-b border-slate-200 px-2 py-2 transition hover:bg-slate-50 dark:border-slate-700/50 dark:hover:bg-[#0a1325]">
                <livewire:questions.show
                    :questionId="$question->id"
                    :key="'question-'.$question->id"
                    :inIndex="true"
                    :pinnable="false"
                    :trending="true"
                />
            </div>
        @empty
            <div class="py-8 text-center">
                <p class="text-lg font-medium text-slate-950 dark:text-white">
                    {{ __('There are no trending questions right now.') }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Check back soon as new questions and discussions gain traction.') }}
                </p>
            </div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$trendingQuestions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
