<div
    class="mb-12 pt-4"
    id="questions-create"
>
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
        x-data="{
            ...imageUpload(),
            ...poll(),
            initComponents() {
                imageUpload().init.call(this);
                poll().init.call(this);
            }
        }"
        x-init='() => {
            uploadLimit = {{ $this->uploadLimit }};
            maxFileSize = {{ $this->maxFileSize }};
            maxContentLength = {{ $this->maxContentLength }};
            initComponents();
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
                    class="p-1.5 rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-800 bg-slate-50 text-sm dark:text-slate-400 text-slate-600 hover:text-pink-500 dark:hover:bg-slate-700 hover:bg-slate-100"
                    :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                >
                    <x-heroicon-o-photo class="h-5 w-5"/>
                </button>
                @if (! $this->parentId && $this->isSharingUpdate)
                    <button
                        type="button"
                        x-on:click="togglePoll()"
                        title="Create a poll"
                        class="p-1.5 rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-800 bg-slate-50 text-sm dark:text-slate-400 text-slate-600 hover:text-pink-500 dark:hover:bg-slate-700 hover:bg-slate-100"
                        :class="{'text-pink-500': isPoll}"
                    >
                        <x-heroicon-o-chart-bar class="h-5 w-5"/>
                    </button>
                @endif
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

        <div x-show="isPoll" class="mt-4 space-y-4">
            <div class="space-y-2">
                <h4 class="text-sm font-medium dark:text-slate-300 text-slate-700">Poll Options</h4>
                <template x-for="(option, index) in pollOptions" :key="index">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600 flex-shrink-0"></div>
                        <x-text-input
                            x-model="pollOptions[index]"
                            ::placeholder="`Option ${index + 1}`"
                            class="flex-1"
                            maxlength="100"
                        />
                        <button
                            x-show="canRemoveOption()"
                            type="button"
                            x-on:click="removePollOption(index)"
                            class="p-1 text-slate-400 hover:text-red-500 transition-colors"
                            title="Remove option"
                        >
                            <x-heroicon-o-x-mark class="h-4 w-4"/>
                        </button>
                    </div>
                </template>

                <button
                    x-show="canAddOption()"
                    type="button"
                    x-on:click="addPollOption()"
                    class="flex items-center gap-1 text-sm text-pink-500 hover:text-pink-600 transition-colors"
                >
                    <x-heroicon-o-plus class="h-4 w-4"/>
                    Add option
                </button>
            </div>

            <div>
                <label for="pollDuration" class="block text-sm font-medium dark:text-slate-300 text-slate-700 mb-2">
                    Poll Duration
                </label>
                <select
                    id="pollDuration"
                    x-model="pollDuration"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-pink-500 dark:focus:border-pink-600 focus:ring-pink-500 dark:focus:ring-pink-600"
                >
                    <option value="">Select duration</option>
                    <option value="1">1 day</option>
                    <option value="2">2 days</option>
                    <option value="3">3 days</option>
                    <option value="5">5 days</option>
                    <option value="7">1 week</option>
                </select>
            </div>
        </div>
    </form>
</div>
