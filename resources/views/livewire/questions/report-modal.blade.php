<div class="p-6">
    <div class="flex items-center justify-between border-b border-slate-200/70 pb-4 dark:border-slate-800/40">
        <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Report post') }}</h3>
        <button
            type="button"
            x-on:click="$dispatch('close-modal', 'question.report.{{ $question->id }}')"
            class="text-slate-400 hover:text-slate-500 focus:outline-none dark:hover:text-slate-300"
        >
            <x-heroicon-o-x-mark class="size-5" />
        </button>
    </div>

    <form wire:submit="submit" class="mt-4 space-y-4">
        <div>
            <label class="block text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                {{ __('Why are you reporting this post?') }}
            </label>
            <div class="mt-2.5 space-y-2.5 text-sm text-slate-700 dark:text-slate-300">
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model.live="category"
                        value="spam"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="font-medium text-slate-950 dark:text-white">{{ __('Spam or misleading') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Promotional spam, scams, bot activity, or deceptive content.') }}
                        </p>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model.live="category"
                        value="harassment"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="font-medium text-slate-950 dark:text-white">{{ __('Harassment or hate speech') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Targeted abuse, threats, discrimination, or hate speech.') }}
                        </p>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model.live="category"
                        value="inappropriate"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="font-medium text-slate-950 dark:text-white">{{ __('Inappropriate content') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Explicit material, violence, or sensitive content.') }}
                        </p>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model.live="category"
                        value="wrong_topic"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="font-medium text-slate-950 dark:text-white">{{ __('Wrong topic classification') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Post belongs in a different topic (currently: :topic).', ['topic' => $question->topic?->name ?? 'None']) }}
                        </p>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model.live="category"
                        value="other"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="font-medium text-slate-950 dark:text-white">{{ __('Other issue') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Any other rules violation.') }}</p>
                    </div>
                </label>
            </div>
        </div>

        @if ($category === 'wrong_topic')
            <div class="rounded-lg border border-pink-100 bg-pink-50/50 p-3 dark:border-pink-950/40 dark:bg-[#10192e]">
                <label
                    for="suggestedTopicId_{{ $question->id }}"
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    {{ __('Suggest the correct topic (optional)') }}
                </label>
                <select
                    id="suggestedTopicId_{{ $question->id }}"
                    wire:model="suggestedTopicId"
                    class="mt-1.5 block w-full rounded-md border border-slate-200/70 bg-white px-3 py-2 text-sm text-slate-950 focus:border-pink-500 focus:ring-pink-500 dark:border-slate-800/40 dark:bg-[#0b1324] dark:text-white"
                >
                    <option value="">{{ __('No specific suggestion') }}</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label
                for="details_{{ $question->id }}"
                class="block text-xs font-medium text-slate-700 dark:text-slate-300"
            >
                {{ __('Additional context (optional)') }}
            </label>
            <x-textarea
                id="details_{{ $question->id }}"
                wire:model="details"
                placeholder="Help us understand the issue..."
                rows="2"
                maxlength="500"
                class="mt-1 block w-full"
            />
        </div>

        <div class="mt-6 flex items-center justify-end gap-3 pt-2">
            <x-secondary-button
                type="button"
                x-on:click="$dispatch('close-modal', 'question.report.{{ $question->id }}')"
            >
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button type="submit"> {{ __('Submit Report') }} </x-primary-button>
        </div>
    </form>
</div>
