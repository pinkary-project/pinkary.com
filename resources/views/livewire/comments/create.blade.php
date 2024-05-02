<div>
    @auth
        <button
                x-on:click="$dispatch('open-modal', 'comment.create')"
                title="Add a comment"
                class="fixed bottom-8 left-8 z-[51] rounded-full bg-pink-500 p-2 text-white">
            <x-icons.plus class="h-6 w-6"/>
        </button>
        <x-modal
                max-width="md"
                show-close-button="false"
                name="comment.create">
            <div class="p-8">
                <h2 class="text-md font-medium text-slate-50">Add Comment</h2>
                <x-comment-form action="store"/>
            </div>
        </x-modal>
    @else
        <a
                href="{{ route('login') }}"
                title="Login to add a comment"
                class="fixed bottom-8 left-8 z-50 rounded-full bg-pink-500 p-2 text-white">
            <x-icons.login class="h-6 w-6"/>
        </a>
    @endauth
</div>
