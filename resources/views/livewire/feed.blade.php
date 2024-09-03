<div>
    <section class="min-h-screen divide-y sm:mb-12 divide-slate-700/70 sm:divide-y-0 sm:space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show
                :questionId="$question->id"
                :key="'question-' . $question->id"
                :inIndex="true"
            />
        @empty
            <div class="text-center text-slate-400">There are no questions to show.</div>
        @endforelse

        <x-load-more-button
            :perPage="$perPage"
            :paginator="$questions"
            message="There are no more questions to load, or you have scrolled too far."
        />
    </section>
</div>
