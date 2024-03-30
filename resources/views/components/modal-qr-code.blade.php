<x-modal name="show-qr-code" maxWidth="lg">
    <div class="p-6">
        <img src="{{ route('qr-code.image') }}" class="w-full max-w-lg mx-auto">

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">Close</x-secondary-button>
        </div>
    </div>
</x-modal>
