<div>
    <section class="mb-12 min-h-screen space-y-10">
        @forelse ($questions as $question)
            <livewire:questions.show
                :questionId="$question->id"
                :key="'question-' . $question->id"
                :inIndex="true"
                :inThread="$hashtag === null"
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
