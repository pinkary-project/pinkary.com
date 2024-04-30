<div>
    @auth
        <div
            x-data="{ openCreateModal: false }"
            x-init="() => { $wire.on('comment.created', () => openCreateModal = false) }"
            @close="openCreateModal = false;">
            <button
                x-on:click="openCreateModal = ! openCreateModal"
                title="Add a comment"
                class="fixed bottom-8 left-8 z-50 rounded-full bg-pink-500 p-2 text-white">
                <x-icons.plus class="h-6 w-6"/>
            </button>
            <div x-show="openCreateModal" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-slate-900 bg-opacity-5 bg-clip-padding backdrop-blur-sm backdrop-filter">
                <div x-on:click.outside="openCreateModal = false" class="w-full max-w-md rounded-lg bg-slate-800 p-8">
                    <div class="border-l border-slate-700">
                         <x-comment-form action="store" />
                    </div>
                </div>
            </div>
        </div>
    @else
        <a
            href="{{ route('login') }}"
            title="Login to add a comment"
            class="fixed bottom-8 left-8 z-50 rounded-full bg-pink-500 p-2 text-white">
            <x-icons.login class="h-6 w-6"/>
        </a>
    @endauth
</div>
