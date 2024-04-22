<x-modal
    name="show-qr-code"
    maxWidth="lg"
>
    <div class="p-6">
        <img
            src="{{ route('qr-code.image') }}"
            class="mx-auto w-full max-w-lg"
        />

        <div class="mt-6 flex justify-end gap-4">
            <x-secondary-button x-on:click="$dispatch('close')">Close</x-secondary-button>
            <x-primary-button
                as="a"
                x-on:click="$dispatch('close')"
                href="{{ route('qr-code.image') }}"
                download
            >
                Download
            </x-primary-button>
        </div>
    </div>
</x-modal>
