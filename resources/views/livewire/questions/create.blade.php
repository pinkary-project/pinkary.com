<div id="questions-create">
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
        x-data="imageUpload"
        x-init='() => {
            uploadLimit = {{ $this->uploadLimit }};
            maxFileSize = {{ $this->maxFileSize }};
            maxContentLength = {{ $this->maxContentLength }};
        }'
        class="p-6 xl:p-8"
    >
        <div>
            <div class="flex space-x-4">
                @auth
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}"
                         class="rounded-full size-10">
                @endauth
                <label for="post" class="sr-only">{{ __('Post') }}</label>
                <div class="flex flex-1 flex-col">
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
                            class="block"
                        />
                    </div>

                    <input class="hidden" type="file" x-ref="imageInput" multiple accept="image/*"/>
                    <input class="hidden" type="file" x-ref="imageUpload" multiple accept="image/*"
                           wire:model="images"/>

                    <div x-show="images.length > 0" class="relative mt-2 flex h-20 flex-wrap gap-2">
                        <template x-for="(image, index) in images" :key="index">
                            <div class="relative h-20 w-20">
                                <img :src="image.path"
                                     :alt="image.originalName"
                                     x-on:click="createMarkdownImage(index)"
                                     title="Reinsert the image"
                                     class="h-full w-full cursor-pointer rounded-lg object-cover"/>
                                <button @click="removeImage($event, index)"
                                        class="absolute rounded-md bg-slate-200 bg-opacity-75 p-1 text-slate-600 top-0.5 right-0.5 hover:text-pink-500 dark:bg-slate-800 dark:text-slate-400">
                                    <x-icons.close class="size-4"/>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex justify-between">
                        <div class="flex space-x-2">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-pink-600 px-12 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                                {{ __('Post') }}
                            </button>

                            <button
                                title="Upload an image"
                                x-ref="imageButton"
                                :disabled="uploading || images.length >= uploadLimit"
                                class="flex items-center justify-center rounded-lg text-gray-400 size-10 hover:bg-gray-800"
                                :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                            >
                                <x-icons.camera class="size-6"/>
                            </button>

                        </div>

                        <div class="flex flex-col justify-center space-y-2">
                            <p class="text-right text-xs text-slate-600 dark:text-slate-400"><span
                                    x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>

                            @if (! $this->parentId && ! $this->isSharingUpdate)
                                <div class="flex items-center">
                                    <x-checkbox
                                        wire:model="anonymously"
                                        id="anonymously"
                                    />

                                    <label
                                        for="anonymously"
                                        class="ml-2 text-xs text-slate-600 dark:text-slate-400"
                                    >{{ __('Anonymously') }}</label>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
