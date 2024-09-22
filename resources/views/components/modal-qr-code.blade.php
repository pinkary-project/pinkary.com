<x-modal
    name="show-qr-code"
    maxWidth="lg"
    showCloseButton="false"
>
    <div class="p-6"
        x-data="{
            link: '{{ route('qr-code.image') }}',
            update(){
                this.link = '{{ route('qr-code.image') }}' + '?theme=' + (isDarkTheme() ? 'dark' : 'light');
            }
        }"
        x-init="update()"
        x-on:open-modal.window="$event.detail == 'show-qr-code' && update()"
    >
        <img
            loading="lazy"
            :src="link"
            class="mx-auto w-full max-w-lg"
        />

        <div class="mt-6 flex justify-end gap-4">
            <x-secondary-button x-on:click="$dispatch('close')">Close</x-secondary-button>
            <x-primary-button
                as="a"
                x-on:click="$dispatch('close')"
                ::href="link"
                download
            >
                Download
            </x-primary-button>
        </div>
    </div>
</x-modal>
