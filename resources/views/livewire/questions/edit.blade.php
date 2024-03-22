<div class="border-l border-gray-900">
    <form wire:submit="update">
        <div class="mt-4 flex items-center justify-between">
            <div class="w-full">
                <div class="mb-1">
                    <textarea
                        wire:model="answer"
                        class="h-24 w-full border-none border-transparent bg-transparent text-white focus:border-transparent focus:outline-0 focus:ring-0"
                        placeholder="Write your answer..."
                        rows="3"
                    ></textarea>
                    @error('answer')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
                <div class="flex items-center justify-between gap-4">
                    <div class="items center ml-2 flex gap-4">
                        <x-primary-colorless-button
                            class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                            type="submit"
                        >
                            {{ __('Send') }}
                        </x-primary-colorless-button>

                        <button
                            wire:click.prevent="destroy"
                            wire:confirm="Are you sure you want to ignore this question?"
                            class="text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Ignore
                        </button>

                        @if (! $question->is_reported)
                            <button
                                wire:click.prevent="report"
                                wire:confirm="Are you sure you want to report this question?"
                                class="text-gray-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Report
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
