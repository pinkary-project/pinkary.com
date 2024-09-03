<div
    class="p-5 sm:mb-12 sm:pt-4 sm:p-0"
    id="questions-create"
>
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
        x-data="imageUpload"
        x-init='() => {
            uploadLimit = {{ $this->uploadLimit }};
            maxFileSize = {{ $this->maxFileSize }};
        }'
    >
        <div
            class="relative group/menu">
                <div x-data="{ content: $persist($wire.entangle('content')).as('{{ $this->draftKey }}') }">
                    <x-textarea
                        x-model="content"
                        placeholder="{{ $this->placeholder }}"
                        maxlength="{{ $this->maxContentLength }}"
                        rows="3"
                        required
                        x-autosize
                        x-ref="content"
                        autocomplete
                    />
                </div>
            <input class="hidden" type="file" x-ref="imageInput" multiple accept="image/*" />
            <input class="hidden" type="file" x-ref="imageUpload" multiple accept="image/*" wire:model="images" />

            <div x-show="images.length > 0" class="relative flex flex-wrap h-20 gap-2 mt-2">
                <template x-for="(image, index) in images" :key="index">
                    <div class="relative w-20 h-20">
                        <img :src="image.path" :alt="image.originalName"
                             x-on:click="createMarkdownImage(index)"
                             title="Reinsert the image"
                             class="object-cover w-full h-full rounded-lg cursor-pointer"/>
                        <button @click="removeImage($event, index)"
                                class="absolute top-0.5 right-0.5 p-1 rounded-md bg-slate-800 bg-opacity-75 text-slate-400 hover:text-pink-500">
                            <x-icons.close class="size-4"/>
                        </button>
                    </div>
                </template>
            </div>

            <p class="text-xs text-right text-slate-400"><span x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>

            <ul>
                <template x-for="(error, index) in errors" :key="index">
                    <li class="w-full py-2 text-sm text-red-600"><span x-text="error"></span></li>
                </template>
            </ul>

        </div>
        <div class="flex items-center justify-between gap-4 mt-4">
            <div class="flex items-center gap-4">
                <x-primary-button
                    class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                    type="submit"
                >
                    {{ __('Send') }}
                </x-primary-button>
                <button
                    title="Upload an image"
                    x-ref="imageButton"
                    :disabled="uploading || images.length >= uploadLimit"
                    class="rounded-lg bg-slate-800 text-sm text-slate-400 p-1.5 hover:text-pink-500"
                    :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                >
                    <x-heroicon-o-camera class="w-5 h-5"/>
                </button>
            </div>
            @if (! $this->parentId && ! $this->isSharingUpdate)
                <div class="flex items-center">
                    <x-checkbox
                        wire:model="anonymously"
                        id="anonymously"
                    />

                    <label
                        for="anonymously"
                        class="ml-2 text-slate-400"
                        >Anonymously</label
                    >
                </div>
            @endif
        </div>
    </form>
</div>
