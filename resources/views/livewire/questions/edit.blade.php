<div class="border-l border-slate-900">
    <form wire:submit="update">
        <div class="mt-4 flex items-center justify-between">
            <div class="w-full">
                <div class="mb-1">
                    <label for="{{ 'answer_question_'.$question->id }}" class="sr-only">Answer</label>

                    <textarea
                        id="{{ 'answer_question_'.$question->id }}"
                        wire:model="answer"
                        x-autosize
                        class="h-24 w-full resize-none border-none border-transparent bg-transparent text-white focus:border-transparent focus:outline-0 focus:ring-0"
                        placeholder="Write your answer..."
                        maxlength="1000"
                        rows="3"
                    ></textarea>

                    <p class="text-right text-xs text-slate-400"><span x-text="$wire.answer.length"></span> / 1000</p>

                    @error('answer')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
                <div class="flex items-center justify-between gap-4">
                    <div class="items center ml-2 flex gap-4">
                        <x-primary-colorless-button class="text-{{ $user->left_color }} border-{{ $user->left_color }}" type="submit">
                            {{ __('Send') }}
                        </x-primary-colorless-button>

                        <button
                            wire:click.prevent="ignore"
                            wire:confirm="Are you sure you want to ignore this question?"
                            class="text-slate-600 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Ignore
                        </button>

                        @if (! $question->is_reported)
                            <button
                                wire:click.prevent="report"
                                wire:confirm="Are you sure you want to report this question?"
                                class="text-slate-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
