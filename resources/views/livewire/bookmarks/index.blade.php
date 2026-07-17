<div class="mb-20">
    @forelse ($bookmarks as $bookmark)
        <div class="border-b border-slate-200 px-0 py-6 first:pt-0 dark:border-slate-700/50">
            <livewire:questions.show
                :questionId="$bookmark->question->id"
                :key="'question-'.$bookmark->question->id"
                :inIndex="true"
            />
        </div>
    @empty
        <div class="flex min-h-96 items-center justify-center rounded-md border border-dashed border-slate-300/80 bg-slate-50/70 px-6 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
            <div>
                <p class="text-lg font-medium text-slate-950 dark:text-white">No bookmarks yet.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Posts you save for later will show up here.
                </p>
            </div>
        </div>
    @endforelse

    <x-load-more-button
        :perPage="$perPage"
        :paginator="$bookmarks"
        message="There are no more bookmarks to load, or you have scrolled too far."
    />
</div>
