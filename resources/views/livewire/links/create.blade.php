<form wire:submit="store">
    <div class="space-y-3">
        <div>
            <x-input-label
                for="description"
                :value="__('Description')"
            />
            <x-text-input
                id="description"
                type="text"
                wire:model="description"
                class="mt-1 block w-full"
                required
                autofocus
            />
            @error('description')
                <x-input-error
                    :messages="$message"
                    class="mt-2"
                />
            @enderror
        </div>
        <div>
            <x-input-label
                for="url"
                :value="__('URL')"
            />
            <x-text-input
                id="url"
                type="text"
                class="mt-1 block w-full"
                wire:model="url"
                required
            />
            @error('url')
                <x-input-error
                    :messages="$message"
                    class="mt-2"
                />
            @enderror
        </div>
        <div class="flex items-center gap-4">
            <x-primary-colorless-button
                class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                type="submit"
            >
                {{ __('Create') }}
            </x-primary-colorless-button>
            <button
                x-on:click="showLinksForm = false"
                type="button"
                class="dark:text-slate-400 text-slate-600 dark:hover:text-slate-600 hover:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Cancel
            </button>
        </div>
    </div>
</form>
