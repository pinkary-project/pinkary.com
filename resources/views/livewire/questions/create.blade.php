<div
    class="relative {{ $this->customDraftKey === 'post_modal' ? 'flex min-h-0 flex-1 flex-col' : '' }}"
    id="questions-create-{{ $this->getId() }}"
>
    @php
        $isModalComposer = $this->customDraftKey === 'post_modal';
        $mainTextareaClasses = $this->canThread
            ? 'resize-none rounded-none! border-0! bg-transparent! px-3.5! py-1.5! pr-9! text-[0.95rem]! leading-7! text-slate-950! shadow-none! placeholder:text-slate-400! focus:ring-0! dark:text-white! dark:placeholder:text-slate-500!'
            : 'min-h-20! resize-none rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!';
    @endphp
    @if ($this->needsCaptcha)
        <x-turnstile.scripts />
    @endif
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
        data-post-composer
        data-draft-key="{{ $this->draftKey }}"
        x-data="questionComposer({ draftKey: '{{ $this->draftKey }}', maxThreadPosts: {{ $this->maxThreadPosts }}, uploadLimit: {{ $this->uploadLimit }}, maxFileSize: {{ $this->maxFileSize }}, maxContentLength: {{ $this->maxContentLength }}, compact: {{ $isModalComposer ? 'false' : 'true' }} })"
        x-on:focusin="expandComposer()"
        class="{{ $isModalComposer ? 'flex min-h-0 flex-1 flex-col' : '' }} pb-0"
    >
        <div x-ref="composerScroll" class="{{ $isModalComposer ? 'min-h-0 flex-1 overflow-y-auto' : '' }}">
            <div class="min-w-0">
                <div class="group/menu relative">
                    <div class="group/main flex gap-3">
                        @if ($this->canThread)
                            <div class="flex w-10 shrink-0 flex-col items-center">
                                <img
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->username ?? '' }}"
                                    loading="lazy"
                                    class="size-10 rounded-full border border-slate-200/70 object-cover dark:border-slate-800"
                                />
                                <div
                                    x-show="threadPosts.length > 0"
                                    style="display: none"
                                    class="mt-1 w-px flex-1 bg-slate-200 transition-colors group-focus-within/main:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/main:bg-pink-500"
                                ></div>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1 p-0">
                            <x-textarea
                                x-model="content"
                                id="mention-main-{{ $this->getId() }}"
                                placeholder="{{ $this->placeholder }}"
                                maxlength="{{ $this->maxContentLength }}"
                                rows="1"
                                required
                                x-autosize
                                x-ref="content"
                                autocomplete
                                class="{{ $mainTextareaClasses }}"
                            />

                            <p
                                x-show="(content || '').length > {{ (int) round($this->maxContentLength * 0.8) }}"
                                style="display: none"
                                :class="
                                (content || '').length >= {{ $this->maxContentLength }}
                                    ? 'text-red-500 dark:text-red-400'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                                class="mt-2 text-right text-sm"
                            >
                                <span x-text="(content || '').length"></span> / {{ $this->maxContentLength }}
                            </p>

                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            <div
                                x-show="images.some((image) => image.target === null)"
                                class="mt-2 flex flex-wrap gap-2"
                            >
                                <template
                                    x-for="image in images.filter((image) => image.target === null)"
                                    :key="image.path"
                                >
                                    <div class="relative size-14 overflow-hidden rounded-lg border border-slate-200/70 dark:border-slate-800/50">
                                        <img
                                            :src="image.path"
                                            :alt="image.originalName"
                                            x-on:click="createMarkdownImage(images.indexOf(image))"
                                            title="Reinsert the image"
                                            class="size-full cursor-pointer object-cover"
                                        />
                                        <button
                                            type="button"
                                            x-on:click="removeImage($event, images.indexOf(image))"
                                            class="absolute top-1 right-1 rounded bg-slate-950/70 p-0.5 text-white transition hover:bg-pink-500"
                                            :aria-label="`Remove ${image.originalName}`"
                                        >
                                            <x-icons.close class="size-3" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div
                                x-cloak
                                x-show="showSecondaryControls()"
                                x-transition
                                class="mt-1 flex items-center gap-1 px-3.5"
                            >
                                <button
                                    type="button"
                                    title="Upload an image"
                                    x-ref="imageButton"
                                    x-on:click="activeImageTarget = null"
                                    :disabled="uploading || images.length >= uploadLimit"
                                    class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-300"
                                >
                                    <x-heroicon-o-photo class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    x-on:click="togglePoll()"
                                    :disabled="uploading"
                                    title="Create a poll"
                                    class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-300"
                                    :class="{ 'text-pink-500': isPoll }"
                                >
                                    <x-heroicon-o-chart-bar class="size-4" />
                                </button>
                            </div>
                            <div
                                x-cloak
                                x-show="isPoll && showSecondaryControls()"
                                class="mt-3 space-y-2 px-3.5"
                                style="display: none"
                            >
                                <template x-for="(option, index) in pollOptions" :key="index">
                                    <div class="flex items-center gap-2">
                                        <span class="size-3.5 shrink-0 rounded-full border border-slate-400 dark:border-slate-600"></span>
                                        <x-text-input
                                            x-model="pollOptions[index]"
                                            ::placeholder="`Option ${index + 1}`"
                                            class="min-w-0 flex-1"
                                            maxlength="40"
                                        />
                                        <button
                                            x-show="canRemoveOption()"
                                            type="button"
                                            x-on:click="removePollOption(index)"
                                            class="rounded-full p-1 text-slate-400 transition hover:text-red-500"
                                            title="Remove option"
                                        >
                                            <x-heroicon-o-x-mark class="size-4" />
                                        </button>
                                    </div>
                                </template>
                                <button
                                    x-show="canAddOption()"
                                    type="button"
                                    x-on:click="addPollOption()"
                                    class="w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-left text-sm text-slate-400 transition hover:border-pink-400 hover:text-pink-500 dark:border-slate-700 dark:text-slate-500"
                                >
                                    Add another option
                                </button>
                                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <label class="flex items-center gap-2">
                                        <span>Duration</span>
                                        <span class="relative">
                                            <select
                                                x-model="pollDuration"
                                                aria-label="Poll duration"
                                                class="appearance-none rounded-lg border border-slate-200/70 bg-white px-2.5 py-1.5 pr-8 text-xs text-slate-600 shadow-sm focus:border-pink-500 focus:ring-pink-500 dark:border-slate-800/70 dark:bg-[#10182b] dark:text-slate-300"
                                            >
                                                <option value="1">24 hours</option>
                                                <option value="2">2 days</option>
                                                <option value="3">3 days</option>
                                                <option value="5">5 days</option>
                                                <option value="7">1 week</option>
                                            </select>
                                            <x-heroicon-o-chevron-down class="pointer-events-none absolute top-1/2 right-2 size-3.5 -translate-y-1/2 text-slate-400" />
                                        </span>
                                    </label>
                                    <button type="button" x-on:click="togglePoll()" class="hover:text-pink-500">
                                        Remove poll
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('pollOptions')" class="text-xs" />
                                <x-input-error :messages="$errors->get('pollDuration')" class="text-xs" />
                            </div>
                        </div>
                    </div>
                    <input class="hidden" type="file" x-ref="imageInput" multiple accept="image/*" />
                    <input
                        class="hidden"
                        type="file"
                        x-ref="imageUpload"
                        multiple
                        accept="image/*"
                        wire:model="images"
                    />

                    <ul>
                        <template x-for="(error, index) in errors" :key="index">
                            <li class="w-full py-2 text-sm text-red-500"><span x-text="error"></span></li>
                        </template>
                    </ul>

                    @if ($this->canThread)
                        <div x-show="threadPosts.length > 0" style="display: none">
                            <template x-for="(post, index) in threadPosts" :key="'thread-post-' + index">
                                <div class="group/post mt-1 flex animate-[thread-post-enter_0.3s_ease-out] gap-3">
                                    <div class="flex w-10 shrink-0 flex-col items-center">
                                        <div class="w-px flex-1 bg-slate-200 transition-colors group-focus-within/post:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/post:bg-pink-500"></div>
                                        <img
                                            src="{{ $user->avatar_url }}"
                                            alt="{{ $user->username ?? '' }}"
                                            loading="lazy"
                                            class="my-1 size-7 rounded-full border border-slate-200/70 object-cover transition-colors group-focus-within/post:border-pink-300 dark:border-slate-800 dark:group-focus-within/post:border-pink-500/50"
                                        />
                                        <div class="w-px flex-1 bg-slate-200 transition-colors group-focus-within/post:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/post:bg-pink-500"></div>
                                    </div>
                                    <div
                                        class="relative min-w-0 flex-1"
                                        :class="{
                                            'rounded-lg bg-red-500/5':
                                                ($wire.errors['threadPosts.' + index] || []).length > 0,
                                        }"
                                    >
                                        <x-textarea
                                            x-model="threadPosts[index]"
                                            data-thread-post
                                            ::id="`mention-thread-${index}-{{ $this->getId() }}`"
                                            x-data="usesDynamicAutocomplete('mention-main-{{ $this->getId() }}')"
                                            x-bind="autocompleteInputBindings"
                                            placeholder="Say more..."
                                            maxlength="{{ $this->maxContentLength }}"
                                            rows="1"
                                            x-autosize
                                            class="resize-none rounded-none! border-0! bg-transparent! px-3.5! py-1.5! pr-9! text-[0.95rem]! leading-7! text-slate-950! shadow-none! placeholder:text-slate-500! focus:ring-0! dark:text-white! dark:placeholder:text-slate-500!"
                                        />
                                        <div
                                            x-show="images.some((image) => image.target === index)"
                                            class="mt-1 flex flex-wrap gap-2 px-3.5"
                                        >
                                            <template
                                                x-for="
                                                    (image, imageIndex) in
                                                    images.filter((image) => image.target === index)
                                                "
                                                :key="image.path"
                                            >
                                                <div class="relative size-14 overflow-hidden rounded-lg border border-slate-200/70 dark:border-slate-800/50">
                                                    <img
                                                        :src="image.path"
                                                        :alt="image.originalName"
                                                        x-on:click="createMarkdownImage(images.indexOf(image))"
                                                        title="Reinsert the image"
                                                        class="size-full cursor-pointer object-cover"
                                                    />
                                                    <button
                                                        type="button"
                                                        x-on:click="removeImage($event, images.indexOf(image))"
                                                        class="absolute top-1 right-1 rounded bg-slate-950/70 p-0.5 text-white transition hover:bg-pink-500"
                                                        :aria-label="`Remove ${image.originalName}`"
                                                    >
                                                        <x-icons.close class="size-3" />
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <div
                                            x-show="threadPolls[index]?.isPoll"
                                            class="mt-2 space-y-2 px-3.5"
                                            style="display: none"
                                        >
                                            <template
                                                x-for="(option, optionIndex) in (threadPolls[index]?.options || [])"
                                                :key="optionIndex"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <span class="size-3.5 shrink-0 rounded-full border border-slate-400 dark:border-slate-600"></span>
                                                    <input
                                                        type="text"
                                                        x-model="threadPolls[index].options[optionIndex]"
                                                        maxlength="40"
                                                        :placeholder="`Option ${optionIndex + 1}`"
                                                        class="min-w-0 flex-1 rounded-lg border-slate-200/70 bg-transparent px-3 py-2 text-sm text-slate-950 shadow-none focus:border-pink-500 focus:ring-pink-500 dark:border-slate-800/60 dark:text-white"
                                                    />
                                                    <button
                                                        x-show="threadPolls[index]?.options?.length > 2"
                                                        type="button"
                                                        x-on:click="removeThreadPollOption(index, optionIndex)"
                                                        class="rounded-full p-1 text-slate-400 transition hover:text-red-500"
                                                        title="Remove option"
                                                    >
                                                        <x-heroicon-o-x-mark class="size-4" />
                                                    </button>
                                                </div>
                                            </template>
                                            <button
                                                x-show="threadPolls[index]?.options?.length < 4"
                                                type="button"
                                                x-on:click="addThreadPollOption(index)"
                                                class="w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-left text-sm text-slate-400 transition hover:border-pink-400 hover:text-pink-500 dark:border-slate-700 dark:text-slate-500"
                                            >
                                                Add another option
                                            </button>
                                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                                <label class="flex items-center gap-2">
                                                    <span>Duration</span>
                                                    <span class="relative">
                                                        <select
                                                            x-model="threadPolls[index].duration"
                                                            aria-label="Poll duration"
                                                            class="appearance-none rounded-lg border border-slate-200/70 bg-white px-2.5 py-1.5 pr-8 text-xs text-slate-600 shadow-sm focus:border-pink-500 focus:ring-pink-500 dark:border-slate-800/70 dark:bg-[#10182b] dark:text-slate-300"
                                                        >
                                                            <option value="1">24 hours</option>
                                                            <option value="2">2 days</option>
                                                            <option value="3">3 days</option>
                                                            <option value="7">1 week</option>
                                                        </select>
                                                        <x-heroicon-o-chevron-down class="pointer-events-none absolute top-1/2 right-2 size-3.5 -translate-y-1/2 text-slate-400" />
                                                    </span>
                                                </label>
                                                <button
                                                    type="button"
                                                    x-on:click="toggleThreadPoll(index)"
                                                    class="hover:text-pink-500"
                                                >
                                                    Remove poll
                                                </button>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            x-on:click="removePost(index)"
                                            title="Remove this post"
                                            aria-label="Remove this post"
                                            class="absolute top-1.5 right-4 rounded-full p-1 text-slate-400 opacity-50 transition hover:text-red-500 hover:opacity-100 focus-visible:opacity-100 dark:text-slate-500 dark:hover:text-red-400"
                                        >
                                            <x-heroicon-o-x-mark class="size-4" />
                                        </button>
                                        <div class="mt-0.5 flex items-center justify-start gap-1 px-3.5">
                                            <button
                                                type="button"
                                                x-on:click="
                                                    activeImageTarget = index;
                                                    $refs.imageInput.click();
                                                "
                                                :disabled="uploading || images.length >= uploadLimit"
                                                title="Add an image to this post"
                                                class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-300"
                                            >
                                                <x-heroicon-o-photo class="size-4" />
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="toggleThreadPoll(index)"
                                                :disabled="uploading"
                                                title="Create a poll for this post"
                                                class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-300"
                                                :class="{ 'text-pink-500': threadPolls[index]?.isPoll }"
                                            >
                                                <x-heroicon-o-chart-bar class="size-4" />
                                            </button>
                                            <p
                                                x-show="(threadPosts[index] || '').length > {{ (int) round($this->maxContentLength * 0.8) }}"
                                                style="display: none"
                                                :class="
                                                (threadPosts[index] || '').length >= {{ $this->maxContentLength }}
                                                    ? 'text-red-500 dark:text-red-400'
                                                    : 'text-slate-500 dark:text-slate-400'
                                            "
                                                class="ml-auto text-right text-xs"
                                            >
                                                <span x-text="(threadPosts[index] || '').length"></span>
                                                / {{ $this->maxContentLength }}
                                            </p>
                                        </div>
                                        <p
                                            x-show="($wire.errors['threadPosts.' + index] || []).length > 0"
                                            style="display: none"
                                            class="mt-1 text-xs text-red-500 dark:text-red-400"
                                            x-text="($wire.errors['threadPosts.' + index] || [])[0]"
                                        ></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button
                            type="button"
                            x-cloak
                            x-show="threadPosts.length < {{ $this->maxThreadPosts - 1 }} && showSecondaryControls()"
                            style="display: none"
                            x-on:click="addPost()"
                            :disabled="! canAddPost()"
                            :title="uploading
                                ? 'Wait for the image upload to finish'
                                : canAddPost()
                                  ? 'Add another post'
                                  : 'Finish your current post first'"
                            class="group/add mt-1 flex w-full items-center gap-3 py-1 text-left transition disabled:cursor-not-allowed"
                            :class="{ 'opacity-40': ! canAddPost() }"
                        >
                            <span class="flex w-10 shrink-0 flex-col items-center">
                                <span class="w-px flex-1 bg-slate-200 dark:bg-slate-700/60"></span>
                                <span class="my-1 flex size-7 items-center justify-center rounded-full border border-dashed border-slate-300 text-slate-400 transition enabled:group-hover/add:border-pink-400 enabled:group-hover/add:bg-pink-50 enabled:group-hover/add:text-pink-500 dark:border-slate-600 dark:text-slate-500 dark:enabled:group-hover/add:border-pink-600 dark:enabled:group-hover/add:bg-pink-500/10 dark:enabled:group-hover/add:text-pink-400">
                                    <x-heroicon-o-plus class="size-4" />
                                </span>
                            </span>
                            <span class="text-sm font-medium text-slate-400 transition enabled:group-hover/add:text-pink-500 dark:text-slate-500 dark:enabled:group-hover/add:text-pink-400">
                                Add another post
                            </span>
                        </button>

                    @endif
                </div>
            </div>
        </div>
        <div class="min-w-0">
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 {{ $isModalComposer ? 'border-t border-slate-200/70 pt-3 dark:border-slate-800/40' : '' }}">
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        :disabled="uploading"
                        class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-slate-800"
                    >
                        @if ($this->parentId)
                            {{ __('Reply') }}
                        @else
                            <span x-show="threadPosts.length === 0">{{ __('Post') }}</span>
                            <span x-show="threadPosts.length > 0" style="display: none">{{ __('Post thread') }}</span>
                        @endif
                    </button>
                    @if (! $this->parentId && $this->isSharingUpdate)
                        <div
                            data-channel-picker
                            wire:ignore
                            x-cloak
                            x-show="showSecondaryControls()"
                            x-transition
                            x-data="{
                                open: false,
                                search: '',
                                selectedChannelId: @js($this->channelId),
                                selectedChannelName: @js($this->selectedChannel?->name ?? ($this->channelName ?? '')),
                                channels: @js($this->availableChannels->map(fn ($channel) => ['id' => $channel->id, 'name' => $channel->name])->values()),
                                searchCache: {},
                                init() {
                                    if (this.selectedChannelId && ! this.selectedChannelName && this.channels) {
                                        const found = this.channels.find((c) => String(c.id) === String(this.selectedChannelId));
                                        if (found) {
                                            this.selectedChannelName = found.name;
                                        }
                                    }
                                    if (this.selectedChannelId && this.selectedChannelName) {
                                        this.$el.setAttribute('data-selected-id', this.selectedChannelId);
                                        this.$el.setAttribute('data-selected-name', this.selectedChannelName);
                                    }
                                    Livewire.on('question.created', () => {
                                        this.select(null);
                                    });
                                    window.addEventListener('channel-selected', (event) => {
                                        this.select(event.detail || null);
                                    });
                                },
                                get filteredChannels() {
                                    if (! this.search.trim()) {
                                        return this.channels.slice(0, 8);
                                    }
                                    const q = this.search.toLowerCase().trim();
                                    return this.channels.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8);
                                },
                                async performSearch() {
                                    const raw = this.search.trim();
                                    if (! raw) return;
                                    const q = raw.toLowerCase();
                                    if (this.searchCache[q]) {
                                        this.searchCache[q].forEach(r => {
                                            if (! this.channels.some(c => String(c.id) === String(r.id))) {
                                                this.channels.push(r);
                                            }
                                        });
                                        return;
                                    }
                                    const results = await this.$wire.searchChannels(raw);
                                    if (Array.isArray(results)) {
                                        this.searchCache[q] = results;
                                        results.forEach(r => {
                                            if (! this.channels.some(c => String(c.id) === String(r.id))) {
                                                this.channels.push(r);
                                            }
                                        });
                                    }
                                },
                                get canCreate() {
                                    const q = this.search.trim();
                                    if (q.length < 2) return false;
                                    return ! this.channels.some(c => c.name.toLowerCase() === q.toLowerCase());
                                },
                                select(channel) {
                                    if (! channel || ! channel.id) {
                                        this.selectedChannelId = null;
                                        this.selectedChannelName = '';
                                        this.$el.removeAttribute('data-selected-id');
                                        this.$el.removeAttribute('data-selected-name');
                                        this.$wire.$set('channelId', null, false);
                                        this.$wire.$set('channelName', null, false);
                                    } else {
                                        const isNew = String(channel.id).startsWith('new:');
                                        this.selectedChannelId = isNew ? channel.id : parseInt(channel.id, 10);
                                        let name = channel.name;
                                        if (! name && this.channels) {
                                            const found = this.channels.find((c) => String(c.id) === String(channel.id));
                                            if (found) {
                                                name = found.name;
                                            }
                                        }
                                        this.selectedChannelName = name || '';
                                        if (this.selectedChannelId && this.selectedChannelName) {
                                            this.$el.setAttribute('data-selected-id', this.selectedChannelId);
                                            this.$el.setAttribute('data-selected-name', this.selectedChannelName);
                                            if (! this.channels.some((c) => String(c.id) === String(this.selectedChannelId))) {
                                                this.channels.push({ id: this.selectedChannelId, name: this.selectedChannelName });
                                            }
                                        }
                                        if (isNew) {
                                            this.$wire.$set('channelId', null, false);
                                            this.$wire.$set('channelName', this.selectedChannelName, false);
                                        } else {
                                            this.$wire.$set('channelId', this.selectedChannelId, false);
                                            this.$wire.$set('channelName', null, false);
                                        }
                                    }
                                    this.open = false;
                                    this.search = '';
                                },
                                async create() {
                                    const name = this.search.trim();
                                    if (name.length < 2) return;
                                    const result = await this.$wire.createChannel(name);
                                    if (result && result.id) {
                                        if (! this.channels.some((c) => String(c.id) === String(result.id))) {
                                            this.channels.push(result);
                                        }
                                        this.select(result);
                                    }
                                }
                            }"
                            x-on:click.outside="open = false"
                            x-on:keydown.escape.window="open = false"
                            class="relative"
                        >
                            <div
                                x-show="selectedChannelId && selectedChannelName"
                                x-cloak
                                class="inline-flex items-center gap-1.5 rounded-lg border border-pink-500/30 bg-pink-500/10 px-2.5 py-1.5 text-xs font-medium text-pink-600 dark:border-pink-500/30 dark:bg-pink-500/15 dark:text-pink-400"
                            >
                                <x-heroicon-o-tag class="size-3.5 shrink-0" />
                                <button
                                    type="button"
                                    x-on:click="
                                        open = ! open;
                                        $nextTick(() => $refs.channelSearch?.focus());
                                    "
                                    class="font-medium hover:underline focus:outline-none"
                                    x-text="selectedChannelName"
                                ></button>
                                <button
                                    type="button"
                                    x-on:click.stop="select(null)"
                                    title="{{ __('Remove channel') }}"
                                    class="ml-0.5 rounded p-0.5 text-pink-500 hover:bg-pink-500/20 hover:text-pink-700 dark:text-pink-400 dark:hover:text-pink-200"
                                >
                                    <x-icons.close class="size-2.5" />
                                </button>
                            </div>

                            <button
                                type="button"
                                x-show="! selectedChannelId || ! selectedChannelName"
                                x-on:click="
                                    open = ! open;
                                    $nextTick(() => $refs.channelSearch?.focus());
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/80 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800/60 dark:bg-[#10182b] dark:text-slate-400 dark:hover:border-slate-700 dark:hover:bg-[#152038] dark:hover:text-white"
                            >
                                <x-heroicon-o-tag class="size-3.5 text-slate-400" />
                                <span>{{ __('Channel') }}</span>
                                <x-heroicon-o-chevron-down class="size-3 text-slate-400" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute bottom-full left-0 z-50 mb-2 w-56 rounded-xl border border-slate-200/90 bg-white p-1.5 shadow-xl backdrop-blur-md dark:border-slate-800/80 dark:bg-[#0c1425]/95 dark:shadow-2xl"
                            >
                                <div class="relative border-b border-slate-100 pb-1.5 dark:border-slate-800/60">
                                    <input
                                        x-ref="channelSearch"
                                        x-model="search"
                                        x-on:input.debounce.250ms="performSearch()"
                                        x-on:keydown.enter.prevent="
                                            if (canCreate) {
                                                create();
                                            } else if (filteredChannels.length > 0) {
                                                select(filteredChannels[0]);
                                            }
                                        "
                                        type="text"
                                        placeholder="{{ __('Search or create channel...') }}"
                                        autocomplete="off"
                                        class="w-full rounded-md border-0 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-1 focus:ring-pink-500 dark:bg-[#152038] dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-[#192744]"
                                    />
                                </div>

                                <div class="max-h-48 overflow-y-auto py-1">
                                    <template x-for="channel in filteredChannels" :key="channel.id">
                                        <button
                                            type="button"
                                            x-on:click="select(channel)"
                                            class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs transition hover:bg-slate-100 dark:hover:bg-slate-800/60"
                                            :class="{
                                                'font-semibold text-pink-600 dark:text-pink-400':
                                                    channel.id === selectedChannelId,
                                            }"
                                        >
                                            <span
                                                x-text="channel.name"
                                                class="truncate text-slate-800 dark:text-slate-200"
                                            ></span>
                                            <template x-if="channel.id === selectedChannelId">
                                                <x-heroicon-m-check class="size-3.5 text-pink-500" />
                                            </template>
                                        </button>
                                    </template>

                                    <template x-if="canCreate">
                                        <button
                                            type="button"
                                            x-on:click="create()"
                                            class="mt-1 flex w-full items-center gap-2 rounded-lg border border-dashed border-pink-300/80 bg-pink-50/50 px-2.5 py-1.5 text-left text-xs font-medium text-pink-600 transition hover:bg-pink-100/70 dark:border-pink-500/30 dark:bg-pink-500/10 dark:text-pink-400 dark:hover:bg-pink-500/20"
                                        >
                                            <x-heroicon-o-plus class="size-3.5 shrink-0" />
                                            <span class="truncate"
                                                >{{ __('Create') }} &quot;<span x-text="search.trim()"></span
                                                >&quot;</span>
                                        </button>
                                    </template>

                                    <div
                                        x-show="filteredChannels.length === 0 && ! canCreate"
                                        class="py-3 text-center text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        <span x-show="! search.trim()">{{ __('Type to create a channel') }}</span>
                                        <span
                                            x-show="search.trim()"
                                            style="display: none"
                                        >{{ __('Min 2 characters required') }}</span>
                                    </div>
                                </div>

                                @error('newChannel')
                                    <p class="px-2 pt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
                @if (! $this->parentId && ! $this->isSharingUpdate)
                    <div class="flex items-center border border-slate-200/70 bg-white px-3 py-2 dark:border-slate-800/30 dark:bg-[#10182b]">
                        <x-checkbox wire:model="anonymously" id="anonymously" />

                        <label for="anonymously" class="ml-2 text-sm text-slate-500 dark:text-slate-400"
                            >Anonymously</label>
                    </div>
                @endif
            </div>
            @if ($this->needsCaptcha)
                <div
                    class="mt-3 rounded-2xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-800/30 dark:bg-[#0b1324]"
                    wire.ignore
                >
                    <div class="flex justify-center">
                        <x-turnstile id="{{ $this->turnstileId }}" wire:model="cfTurnstileResponse" data-theme="auto" />
                    </div>

                    <x-input-error :messages="$errors->get('cfTurnstileResponse')" class="mt-3 text-center" />
                </div>
            @endif
        </div>
    </form>
</div>
