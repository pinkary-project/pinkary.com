<div>
    <form wire:submit="update">
        <div class="mt-12">
            <label class="text-base font-semibold text-gray-500">Link Shape</label>
            <p class="text-sm text-gray-500">In what shape do you want to present your links?</p>
            <fieldset class="mt-4">
                <legend class="sr-only">Shape for links</legend>
                <div class="space-y-4">
                    @foreach (['rounded-none' => 'Square', 'rounded-lg' => 'Round', 'rounded-full' => 'Extra Round'] as $shape => $label)
                        <div class="flex items-center">
                            <input
                                id="{{ strtolower($shape) }}"
                                wire:model="link_shape"
                                type="radio"
                                value="{{ $shape }}"
                                class="text-{{ $user->left_color }} focus:ring-{{ $user->left_color }} h-4 w-4 border-gray-300"
                            />

                            <label
                                for="{{ strtolower($shape) }}"
                                class="ml-3 block text-sm font-medium leading-6 text-gray-500"
                            >
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach

                    @error('link_shape')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
            </fieldset>
        </div>

        <div class="mt-12">
            <label class="text-base font-semibold text-gray-500">Link Color</label>
            <p class="text-sm text-gray-500">What color are you choosing for your links?</p>
            <fieldset class="mt-4">
                <legend class="sr-only">Link color</legend>
                <div class="space-y-4">
                    @foreach ([
                                  'from-blue-500 to-teal-700',
                                  'from-red-500 to-orange-600',
                                  'from-blue-500 to-purple-600',
                                  'from-blue-500 to-teal-700',
                                  'from-red-500 to-orange-600',
                                  'from-purple-500 to-pink-500',
                                  'from-indigo-500 to-lime-700',
                                  'from-yellow-600 to-blue-600',
                              ] as $gradient)
                        <div class="flex justify-between">
                            <input
                                class="text-{{ $user->left_color }} focus:ring-{{ $user->left_color }} mr-3 mt-2 border-gray-300"
                                type="radio"
                                wire:model="gradient"
                                name="gradient"
                                id="{{ $gradient }}"
                                value="{{ $gradient }}"
                            />
                            <label
                                for="{{ $gradient }}"
                                class="{{ $gradient }} from-indigo-400_ to-blue-500_ relative block w-full cursor-pointer rounded-lg border bg-white bg-gradient-to-r px-6 py-4 shadow-sm focus:outline-none sm:flex sm:justify-between"
                            >
                                <span
                                    class="border-pink-600__ pointer-events-none absolute -inset-px rounded-lg border-2"
                                    aria-hidden="true"
                                ></span>
                            </label>
                        </div>
                    @endforeach

                    @error('gradient')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
            </fieldset>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <x-primary-colorless-button
                class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                type="submit"
            >
                {{ __('Save') }}
            </x-primary-colorless-button>
            <button
                @click="showSettingsForm = false"
                type="button"
                class="text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Cancel
            </button>
        </div>
    </form>
</div>
