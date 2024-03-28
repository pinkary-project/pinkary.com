<div class="mb-12 pt-4" id="questions-create">
    <form wire:submit="store">
        <div
            x-data="{
                content: '',
                get characterCount() {
                    return this.content.length;
                }
            }"
        >
            <x-textarea
                wire:model="content"
                x-ref="content"
                x-model="content"
                placeholder="Ask a question..."
                maxlength="255"
                rows="3"
                required/>

            <p x-ref="characterCount" class="text-xs text-right text-slate-400">
                <span x-text="characterCount"></span> / 255
            </p>

            @error('content')
            <x-input-error :messages="$message" class="my-2"/>
            @enderror
        </div>
        <div class="mt-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-primary-button class="text-{{ $user->left_color }} border-{{ $user->left_color }}" type="submit">
                    {{ __('Send') }}
                </x-primary-button>
            </div>
            <div class="flex items-center">
                <x-checkbox wire:model="anonymously" id="anonymously"/>

                <label for="anonymously" class="ml-2 text-slate-600">Anonymously</label>
            </div>
        </div>
    </form>
</div>
