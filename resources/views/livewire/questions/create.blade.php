<div class="mb-12 pt-4" id="questions-create">
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
        x-data="imageUpload"
        x-init="
            () => {
                uploadLimit = {{ $this->uploadLimit }}
                maxFileSize = {{ $this->maxFileSize }}
            }
        "
    >
        <div class="group/menu relative">
            <div
                x-data="{
                    content: $persist($wire.entangle('content')).as('{{ $this->draftKey }}'),
                }"
            >
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

            <div x-show="images.length > 0" class="relative mt-2 flex h-20 flex-wrap gap-2">
                <template x-for="(image, index) in images" :key="index">
                    <div class="relative h-20 w-20">
                        <img
                            :src="image.path"
                            :alt="image.originalName"
                            x-on:click="createMarkdownImage(index)"
                            title="Reinsert the image"
                            class="h-full w-full cursor-pointer rounded-lg object-cover"
                        />
                        <button
                            @click="removeImage($event, index)"
                            class="absolute right-0.5 top-0.5 rounded-md bg-slate-200 bg-opacity-75 p-1 text-slate-600 hover:text-pink-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            <x-icons.close class="size-4" />
                        </button>
                    </div>
                </template>
            </div>

            <p class="text-right text-xs text-slate-600 dark:text-slate-400">
                <span x-text="$wire.content.length"></span>
                / {{ $this->maxContentLength }}
            </p>

            <ul>
                <template x-for="(error, index) in errors" :key="index">
                    <li class="w-full py-2 text-sm text-red-600"><span x-text="error"></span></li>
                </template>
            </ul>
        </div>
        <div class="mt-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-primary-button class="text-{{ $user->left_color }} border-{{ $user->left_color }}" type="submit">
                    {{ __('Send') }}
                </x-primary-button>
                <button
                    title="Upload an image"
                    x-ref="imageButton"
                    :disabled="uploading || images.length >= uploadLimit"
                    class="rounded-lg border border-slate-200 bg-slate-50 p-1.5 text-sm text-slate-600 hover:bg-slate-100 hover:text-pink-500 dark:border-transparent dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
                    :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                >
                    <x-heroicon-o-photo class="h-5 w-5" />
                </button>
            </div>
            @if (! $this->parentId && ! $this->isSharingUpdate)
                <div class="flex items-center">
                    <x-checkbox wire:model="anonymously" id="anonymously" />

                    <label for="anonymously" class="ml-2 text-slate-600 dark:text-slate-400">Anonymously</label>
                </div>
            @endif
        </div>
    </form>
</div>
