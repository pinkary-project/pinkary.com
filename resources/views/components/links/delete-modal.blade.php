<x-modal name="delete-link" maxWidth="sm">
    <div class="p-6" x-data="{ linkId: null }" @set-link-id.window="linkId = $event.detail.id">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Delete Link</h3>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Are you sure you want to delete this link?</p>
        <div class="mt-5 flex justify-end gap-3">
            <button
                x-on:click="$dispatch('close-modal', 'delete-link')"
                type="button"
                class="rounded-md border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            >
                Cancel
            </button>
            <button
                wire:click="destroy(linkId)"
                type="button"
                class="rounded-md bg-red-500 px-4 py-2 text-sm text-white hover:bg-red-400"
            >
                Delete
            </button>
        </div>
    </div>
</x-modal>
