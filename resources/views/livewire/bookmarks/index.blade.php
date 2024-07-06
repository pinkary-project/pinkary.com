<div class="mb-20 flex flex-col gap-2">
    @foreach ($bookmarks as $bookmark)
        <!--  -->
    @endforeach

    @if ($bookmarks->count() === 0)
        <div class="rounded-lg">
            <p class="text-slate-400">No bookmarks.</p>
        </div>
    @endif
</div>
