<div
    class="mb-12 pt-4"
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
            maxContentLength = {{ $this->maxContentLength }};
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

            <div x-show="images.length > 0" class="relative mt-2 flex h-20 flex-wrap gap-2">
                <template x-for="(image, index) in images" :key="index">
                    <div class="relative h-20 w-20">
                        <img :src="image.path" :alt="image.originalName"
                             x-on:click="createMarkdownImage(index)"
                             title="Reinsert the image"
                             class="h-full w-full rounded-lg object-cover cursor-pointer"/>
                        <button @click="removeImage($event, index)"
                                class="absolute top-0.5 right-0.5 p-1 rounded-md dark:bg-slate-800 bg-slate-200 bg-opacity-75 dark:text-slate-400 text-slate-600 hover:text-pink-500">
                            <x-icons.close class="size-4"/>
                        </button>
                    </div>
                </template>
            </div>

            <p class="text-right text-xs dark:text-slate-400 text-slate-600"><span x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>

            <ul>
                <template x-for="(error, index) in errors" :key="index">
                    <li class="py-2 text-sm text-red-600 w-full"><span x-text="error"></span></li>
                </template>
            </ul>

        </div>
        <div class="mt-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 w-full">
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
                    class="p-1.5 rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-800 bg-slate-50 text-sm dark:text-slate-400 text-slate-600 hover:text-pink-500 dark:hover:bg-slate-700 hover:bg-slate-100"
                    :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                >
                    <x-heroicon-o-photo class="h-5 w-5"/>
                </button>

                  {{-- Emoji picker - Hidden on small screens --}}
                  <div 
                  class="ml-auto items-center hidden md:flex" 
                 x-data="{ 
                     'openEmojiPicker': false,
                     'inputField': document.querySelector(`#questions-create [x-ref='content']`)
                  }" 
                 {{-- Listen to emoji clicked event using alpine --}}
                 @emoji-click="
                const emoji = $event.detail['unicode'];

                if (inputField) {
                //make sure that Alpine completes any pending updates to its reactive model
                $nextTick(() => {

                    // Check if the input field is the active element, focus if not
                    // ensures that the input field is focused during the emoji insertion. 
                    // Without this, the cursor position might reset, leading to insertion at the beginning of the text
                   if (document.activeElement !== inputField) {
                        inputField.focus();
                    }

                    // Capture the current cursor position
                    const startPos = inputField.selectionStart;
                    const endPos = inputField.selectionEnd;

                    // Read and modify the current value of the input field
                    const inputFieldValue = inputField.value ?? '';
                    const newValue = inputFieldValue.substring(0, startPos) + emoji + inputFieldValue.substring(endPos);

                    // Update the Alpine model
                    inputField._x_model.set(newValue);

                    // Restore cursor position after Alpine processes updates
                    requestAnimationFrame(() => {
                        inputField.setSelectionRange(startPos + emoji.length, startPos + emoji.length);
                    });
                    });

                } else {
                    console.error('Textarea not found.');
                }
            "
                   @click.away="openEmojiPicker=false">
                 {{-- anchor to inputField so it can be centerlly aligned as text grows --}}
                 <section  x-anchor.right.offset.18="inputField" x-cloak x-show="openEmojiPicker" x-transition:enter
                     class=" flex py-2  py-1.5 dark:border-gray-700 top-20 ">
             
                     <emoji-picker  class=" flex rounded-xl"></emoji-picker>
                 </section>
                 <button wire:loading.attr="disabled" wire:target="store" type="button" dusk="emoji-trigger-button" @click="openEmojiPicker = ! openEmojiPicker"
                         x-ref="emojibutton" 
                         x-bind:class="openEmojiPicker?'text-pink-500 dark:text-pink-500':'text-slate-600 dark:text-gray-400'"
                         class="disabled:cursor-progress rounded-full p-px  hover:text-pink-500 transition-colors "
                         >
                        <svg  viewBox="0 0 24 24"
                         height="24" width="24" preserveAspectRatio="xMidYMid meet"
                         class="w-6 h-6 stroke-[1.3] dark:stroke-[0.7]" version="1.1"
                         x="0px" y="0px" enable-background="new 0 0 24 24">
                         <path fill="currentColor"
                             d="M9.153,11.603c0.795,0,1.439-0.879,1.439-1.962S9.948,7.679,9.153,7.679 S7.714,8.558,7.714,9.641S8.358,11.603,9.153,11.603z M5.949,12.965c-0.026-0.307-0.131,5.218,6.063,5.551 c6.066-0.25,6.066-5.551,6.066-5.551C12,14.381,5.949,12.965,5.949,12.965z M17.312,14.073c0,0-0.669,1.959-5.051,1.959 c-3.505,0-5.388-1.164-5.607-1.959C6.654,14.073,12.566,15.128,17.312,14.073z M11.804,1.011c-6.195,0-10.826,5.022-10.826,11.217 s4.826,10.761,11.021,10.761S23.02,18.423,23.02,12.228C23.021,6.033,17.999,1.011,11.804,1.011z M12,21.354 c-5.273,0-9.381-3.886-9.381-9.159s3.942-9.548,9.215-9.548s9.548,4.275,9.548,9.548C21.381,17.467,17.273,21.354,12,21.354z  M15.108,11.603c0.795,0,1.439-0.879,1.439-1.962s-0.644-1.962-1.439-1.962s-1.439,0.879-1.439,1.962S14.313,11.603,15.108,11.603z">
                         </path>
                     </svg>
                 </button>
               </div>
            </div>
            @if (! $this->parentId && ! $this->isSharingUpdate)
                <div class="flex items-center">
                    <x-checkbox
                        wire:model="anonymously"
                        id="anonymously"
                    />

                    <label
                        for="anonymously"
                        class="ml-2 dark:text-slate-400 text-slate-600"
                        >Anonymously</label
                    >
                </div>
            @endif
        </div>
    </form>
</div>
