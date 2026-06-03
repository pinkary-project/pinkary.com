<div
    class=""
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
        class="pb-0"
    >
        <div class="min-w-0">
                <div class="relative group/menu">
                        <div x-data="{ content: $persist($wire.entangle('content')).as('{{ $this->draftKey }}') }" class="p-0">
                            <x-textarea
                                x-model="content"
                                placeholder="{{ $this->placeholder }}"
                                maxlength="{{ $this->maxContentLength }}"
                                rows="3"
                                required
                                x-autosize
                                x-ref="content"
                                autocomplete
                                class="min-h-20! rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!"
                            />

                            <p class="mt-2 text-right text-sm text-slate-500 dark:text-slate-400"><span x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>
                        </div>
                    <input class="hidden" type="file" x-ref="imageInput" multiple accept="image/*" />
                    <input class="hidden" type="file" x-ref="imageUpload" multiple accept="image/*" wire:model="images" />

                    <div x-show="images.length > 0" class="relative mt-3 flex flex-wrap gap-2">
                        <template x-for="(image, index) in images" :key="index">
                            <div class="relative h-20 w-20">
                                <img :src="image.path" :alt="image.originalName"
                                     x-on:click="createMarkdownImage(index)"
                                     title="Reinsert the image"
                                     class="h-full w-full object-cover cursor-pointer"/>
                                <button @click="removeImage($event, index)"
                                        class="absolute top-0.5 right-0.5 bg-white/90 p-1 text-slate-500 hover:text-pink-500 dark:bg-[#050d1b]/80 dark:text-slate-400">
                                    <x-icons.close class="size-4"/>
                                </button>
                            </div>
                        </template>
                    </div>

                    <ul>
                        <template x-for="(error, index) in errors" :key="index">
                            <li class="py-2 text-sm text-red-500 w-full"><span x-text="error"></span></li>
                        </template>
                    </ul>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white dark:hover:bg-slate-800"
                        >
                            {{ $this->parentId ? __('Reply') : __('Post') }}
                        </button>
                <button
                    title="Upload an image"
                    x-ref="imageButton"
                    :disabled="uploading || images.length >= uploadLimit"
                    class="flex size-10 items-center justify-center border border-slate-200/70 bg-white text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-400 dark:hover:bg-[#162038] dark:hover:text-white"
                    :class="{'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit}"
                >
                    <x-heroicon-o-photo class="h-5 w-5"/>
                </button>
                @if (! $this->parentId && $this->isSharingUpdate)
                    <button
                        type="button"
                        x-on:click="togglePoll()"
                        title="Create a poll"
                        class="flex size-10 items-center justify-center border border-slate-200/70 bg-white text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-400 dark:hover:bg-[#162038] dark:hover:text-white"
                        :class="{'text-pink-500': isPoll}"
                    >
                        <x-heroicon-o-chart-bar class="h-5 w-5"/>
                    </button>
                @endif
                    </div>
                    @if (! $this->parentId && ! $this->isSharingUpdate)
                <div class="flex items-center border border-slate-200/70 bg-white px-3 py-2 dark:border-slate-800/30 dark:bg-[#10182b]">
                    <x-checkbox
                        wire:model="anonymously"
                        id="anonymously"
                    />

                    <label
                        for="anonymously"
                        class="ml-2 text-sm text-slate-500 dark:text-slate-400"
                        >Anonymously</label
                    >
                </div>
                    @endif
                </div>
        </div>

        <div x-show="isPoll" class="mt-4 space-y-4">
            <div class="space-y-2">
                <h4 class="text-sm font-medium dark:text-slate-300 text-slate-700">Poll Options</h4>
                <template x-for="(option, index) in pollOptions" :key="index">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600 shrink-0"></div>
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
