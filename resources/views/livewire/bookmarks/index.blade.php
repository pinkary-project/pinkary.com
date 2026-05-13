<div class="mb-20 divide-y divide-slate-800/30">
    @forelse ($bookmarks as $bookmark)
        <div class="px-0 py-6">
            <livewire:questions.show
                :questionId="$bookmark->question->id"
                :key="'question-' . $bookmark->question->id"
                :inIndex="true"
            />
        </div>
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
