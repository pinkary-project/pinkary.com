<div>
    <x-modal
        name="confirm-password"
        maxWidth="sm"
    >
    <div class="p-6 rounded-lg shadow sm:p-8">
        <div class="mb-4 text-sm text-slate-400">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form
            wire:submit='confirm'
        >
            <div>
                <x-input-label
                    for="password"
                    :value="__('Password')"
                />

                <x-text-input
                    id="password"
                    class="mt-1 block w-full"
                    type="password"
                    name="password"
                    wire:model="password"
                    required
                    autocomplete="current-password"
                />

                <x-input-error
                    :messages="$errors->get('password')"
                    class="mt-2"
                />
            </div>

            <div class="mt-4 flex justify-end">
                <x-primary-button>
                    {{ __('Confirm') }}
                </x-primary-button>
                <x-secondary-button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'confirm-password')"
                    class="ml-2"
                >
                    {{ __('Cancel') }}
                </x-secondary-button>
            </div>
        </form>
    </div>
    </x-modal>
</div>
