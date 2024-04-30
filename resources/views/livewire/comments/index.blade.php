<div>
    <section class="mb-12 min-h-screen space-y-10">
        @forelse ($comments as $comment)
            <livewire:comments.show
                :commentId="$comment->id"
                :key="'comment-' . $comment->id"
                :inIndex="true"
            />
        @empty
            <div class="text-center text-slate-400">No one has commented on this question yet.</div>
        @endforelse
        <x-load-more-button
            :perPage="$perPage"
            :paginator="$comments"
            message="There are no more comments to load, or you have scrolled too far."
        />
    </section>
    <livewire:comments.edit />
    <livewire:comments.delete />
</div>
