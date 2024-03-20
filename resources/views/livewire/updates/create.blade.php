<div class="mb-12 pt-4" id="questions-create">
    <form wire:submit="store">
        <div>
            <textarea
                wire:model="answer"
                class="h-24 w-full rounded-lg border-transparent bg-gray-950 px-1 text-white focus:border-transparent focus:outline-0 focus:ring-0"
                placeholder="I have news about..."
                rows="3"
                required
            ></textarea>
            @error('answer')
                <x-input-error :messages="$message" class="my-2" />
            @enderror
        </div>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-primary-colorless-button
                    class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                    type="submit"
                >
                    {{ __('Share') }}
                </x-primary-colorless-button>
            </div>
        </div>
    </form>
</div>
