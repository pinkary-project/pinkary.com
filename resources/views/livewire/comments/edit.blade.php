<div>
    @if ($isOpen)
        <div
            x-data="{ openEditModal: true }"
            x-init="() => { $wire.on('comment.updated', () => openEditModal = false) }"
            @close="openEditModal = false;"
        >
            <div x-show="openEditModal" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-slate-900 bg-opacity-5 bg-clip-padding backdrop-blur-sm backdrop-filter">
                <div x-on:click.outside="openEditModal = false" class="w-full max-w-md rounded-lg bg-slate-800 p-8">
                    <div class="border-l border-slate-700">
                        <x-comment-form action="update"/>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
