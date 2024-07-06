<div class="mb-20 space-y-10">
    @foreach ($bookmarks as $bookmark)
        <livewire:questions.show
            :questionId="$bookmark->question->id"
            :key="'question-' . $bookmark->question->id"
            :inIndex="true"
        />
    @endforeach

    @if ($bookmarks->count() === 0)
        <div class="rounded-lg">
            <p class="text-slate-400">No bookmarks.</p>
        </div>
    @endif
</div>
