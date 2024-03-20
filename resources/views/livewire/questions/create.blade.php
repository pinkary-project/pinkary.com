<div class="mb-12 pt-4" id="questions-create">
    <form wire:submit="store">
        <div>
            <textarea
                wire:model="content"
                class="h-24 w-full rounded-lg border-transparent bg-gray-950 px-1 text-white focus:border-transparent focus:outline-0 focus:ring-0"
                placeholder="Ask a question..."
                rows="3"
                required
            ></textarea>
            @error('content')
                <x-input-error :messages="$message" class="my-2" />
            @enderror
        </div>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-primary-colorless-button
                    class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                    type="submit"
                >
                    {{ __('Send') }}
                </x-primary-colorless-button>
            </div>
            <div class="items center mt-3 flex">
                <input
                    type="checkbox"
                    wire:model="anonymously"
                    id="anonymously"
                    class="mt-1 rounded border-gray-800 bg-gray-800 text-gray-500 focus:ring-gray-700"
                />
                <label for="anonymously" class="ml-2 text-gray-600">Anonymously</label>
            </div>
        </div>
    </form>
</div>
