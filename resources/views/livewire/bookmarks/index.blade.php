<div class="mb-20 space-y-10">
    @forelse ($bookmarks as $bookmark)
        <livewire:questions.show
            :questionId="$bookmark->question->id"
            :key="'question-' . $bookmark->question->id"
            :inIndex="true"
        />
    @empty
        <div class="rounded-lg">
            <p class="text-slate-400">No bookmarks.</p>
        </div>
    @endforelse

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$bookmarks"
        message="There are no more bookmarks to load, or you have scrolled too far."
    />
</div>
