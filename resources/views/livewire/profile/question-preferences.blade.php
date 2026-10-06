<div>
    <h2 class="text-lg font-medium text-slate-600 dark:text-slate-400">{{ __('Who can ask you questions?') }}</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __($options[$user->question_preference->value]) }}</p>

    <x-secondary-button wire:click="edit" class="mt-4" wire:loading.attr="disabled">
        {{ __('Change') }}
    </x-secondary-button>

    <x-modal
        name="question-preferences"
        maxWidth="md"
        :showCloseButton="false"
        focusable
        role="dialog"
        aria-modal="true"
        aria-labelledby="question-preferences-title"
    >
        <form wire:submit="update" class="p-6">
            <fieldset>
                <legend id="question-preferences-title" class="text-lg font-medium text-slate-950 dark:text-white">
                    {{ __('Who can ask you questions?') }}
                </legend>
                <div class="mt-6 space-y-4">
                    @foreach ($options as $value => $label)
                        <label
                            for="question-preference-{{ $value }}"
                            class="flex cursor-pointer items-start gap-3"
                            wire:key="question-preference-{{ $value }}"
                        >
                            <input
                                id="question-preference-{{ $value }}"
                                name="question-preference"
                                type="radio"
                                wire:model="questionPreference"
                                value="{{ $value }}"
                                class="mt-1 h-4 w-4 border-slate-300 text-pink-500 focus:ring-pink-500 dark:border-slate-700 dark:bg-slate-900"
                            />
                            <span>
                                <span class="block text-sm font-medium text-slate-950 dark:text-white">{{ __($label) }}</span>
                                <span class="mt-1 block text-sm text-slate-500 dark:text-slate-400">
                                    {{
                                        __(match ($value) {
                                            'everyone' => 'Anyone can ask you a question.',
                                            'following' => 'Only people you follow can ask you a question.',
                                            'no_one' => 'Do not allow new questions.',
                                        })
                                    }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <x-input-error :messages="$errors->get('questionPreference')" class="mt-2" />

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'question-preferences')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button wire:loading.attr="disabled">{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
