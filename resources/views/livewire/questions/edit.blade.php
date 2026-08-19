<div>
    <form wire:submit="update" wire:keydown.cmd.enter="update" wire:keydown.ctrl.enter="update" class="pb-0">
        <div class="min-w-0">
            <div class="group/menu relative">
                <div class="p-0">
                    <label for="{{ 'answer_question_'.$question->id }}" class="sr-only">Answer</label>

                    <x-textarea
                        id="{{ 'answer_question_'.$question->id }}"
                        wire:model="answer"
                        x-autosize
                        class="min-h-20! resize-none rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!"
                        placeholder="Write your answer..."
                        maxlength="1000"
                        rows="3"
                        autocomplete
                    ></x-textarea>

                    <p class="mt-2 text-right text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="$wire.answer.length"></span> / 1000
                    </p>

                    @error('answer')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
            </div>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white dark:hover:bg-slate-800"
                    >
                        {{ __('Send') }}
                    </button>

                    @if (! $question->is_reported)
                        @if (! $question->answer)
                            <button
                                wire:click.prevent="ignore"
                                wire:confirm="Are you sure you want to ignore this question?"
                                class="text-sm text-slate-400 hover:text-slate-500 focus:outline-none"
                            >
                                {{ __('Ignore') }}
                            </button>
                            <button
                                wire:click.prevent="report"
                                wire:confirm="Are you sure you want to report this question?"
                                class="text-sm text-slate-400 hover:text-red-500 focus:outline-none"
                            >
                                {{ __('Report') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
