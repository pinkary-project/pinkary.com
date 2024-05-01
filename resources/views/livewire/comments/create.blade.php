<div>
    @auth
        <button
            x-on:click="$dispatch('open-modal', { name: 'comment.create' })"
            title="Add a comment"
            class="fixed bottom-8 left-8 z-50 rounded-full bg-pink-500 p-2 text-white">
            <x-icons.plus class="h-6 w-6"/>
        </button>
        <x-comment-modal name="comment.create">
            <div class="border-l border-slate-700">
                <x-comment-form action="store"/>
            </div>
        </x-comment-modal>
    @else
        <a
            href="{{ route('login') }}"
            title="Login to add a comment"
            class="fixed bottom-8 left-8 z-50 rounded-full bg-pink-500 p-2 text-white">
            <x-icons.login class="h-6 w-6"/>
        </a>
    @endauth
</div>
