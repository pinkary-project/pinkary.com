<div class="" id="questions-create-{{ $this->getId() }}">
    @if ($this->needsCaptcha)
        <x-turnstile.scripts />
    @endif
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
            },
            content: $persist($wire.entangle('content')).as('{{ $this->draftKey }}'),
            threadPosts: $persist($wire.entangle('threadPosts')).as('{{ $this->draftKey }}_posts'),
            addPost() {
                if (this.$wire.customDraftKey !== 'post_modal') {
                    this.continueToModal();

                    return;
                }

                if (this.threadPosts.length < {{ $this->maxThreadPosts - 1 }}) {
                    this.threadPosts.push('');

                    this.focusLastPost();
                }
            },
            continueToModal() {
                const content = (this.content || '').trim();
                const threadPosts = (this.threadPosts || []).filter((post) => (post || '').trim() !== '');

                if (content !== '' || threadPosts.length > 0) {
                    this.$wire.dispatch('thread.continue-in-modal', {
                        content: content,
                        threadPosts: threadPosts,
                    });

                    this.content = '';
                    this.threadPosts = [];
                }

                this.$dispatch('open-modal', 'post-create');
            },
            removePost(index) {
                this.threadPosts.splice(index, 1);
            },
            focusLastPost() {
                this.$nextTick(() => {
                    const textareas = this.$root.querySelectorAll('[data-thread-post]');

                    textareas[textareas.length - 1]?.focus();
                });
            },
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
            <div class="group/menu relative">
                <div class="flex gap-3">
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
                                class="mt-1 w-px flex-1 bg-slate-200 transition-colors group-focus-within/menu:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/menu:bg-pink-500"
                            ></div>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1 p-0">
                        <x-textarea
                            x-model="content"
                            placeholder="{{ $this->placeholder }}"
                            maxlength="{{ $this->maxContentLength }}"
                            rows="3"
                            required
                            x-autosize
                            x-ref="content"
                            autocomplete
                            class="min-h-20! resize-none rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!"
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
                    </div>
                </div>
                <input class="hidden" type="file" x-ref="imageInput" multiple accept="image/*" />
                <input class="hidden" type="file" x-ref="imageUpload" multiple accept="image/*" wire:model="images" />

                <div x-show="images.length > 0" class="relative mt-3 flex flex-wrap gap-2">
                    <template x-for="(image, index) in images" :key="index">
                        <div class="relative h-20 w-20">
                            <img
                                :src="image.path"
                                :alt="image.originalName"
                                x-on:click="createMarkdownImage(index)"
                                title="Reinsert the image"
                                class="h-full w-full cursor-pointer object-cover"
                            />
                            <button
                                @click="removeImage($event, index)"
                                class="absolute top-0.5 right-0.5 bg-white/90 p-1 text-slate-500 hover:text-pink-500 dark:bg-[#050d1b]/80 dark:text-slate-400"
                            >
                                <x-icons.close class="size-4" />
                            </button>
                        </div>
                    </template>
                </div>

                <ul>
                    <template x-for="(error, index) in errors" :key="index">
                        <li class="w-full py-2 text-sm text-red-500"><span x-text="error"></span></li>
                    </template>
                </ul>

                @if ($this->canThread)
                    <div x-show="threadPosts.length > 0" style="display: none">
                        <template x-for="(post, index) in threadPosts" :key="'thread-post-' + index">
                            <div class="mt-3 flex gap-3">
                                <div class="flex w-10 shrink-0 flex-col items-center">
                                    <div class="w-px flex-1 bg-slate-200 transition-colors group-focus-within/menu:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/menu:bg-pink-500"></div>
                                    <img
                                        src="{{ $user->avatar_url }}"
                                        alt="{{ $user->username ?? '' }}"
                                        loading="lazy"
                                        class="my-1 size-7 rounded-full border border-slate-200/70 object-cover transition-colors group-focus-within/menu:border-pink-300 dark:border-slate-800 dark:group-focus-within/menu:border-pink-500/50"
                                    />
                                    <div class="w-px flex-1 bg-slate-200 transition-colors group-focus-within/menu:bg-pink-400 dark:bg-slate-700/60 dark:group-focus-within/menu:bg-pink-500"></div>
                                </div>
                                <div class="relative min-w-0 flex-1">
                                    <x-textarea
                                        x-model="threadPosts[index]"
                                        data-thread-post
                                        placeholder="Say more..."
                                        maxlength="{{ $this->maxContentLength }}"
                                        rows="2"
                                        x-autosize
                                        class="resize-none rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! pr-9! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!"
                                    />
                                    <button
                                        type="button"
                                        x-on:click="removePost(index)"
                                        title="Remove this post"
                                        aria-label="Remove this post"
                                        class="absolute top-2 right-2 rounded-full bg-white/90 p-1 text-slate-400 opacity-50 transition hover:bg-slate-100 hover:text-red-500 hover:opacity-100 focus-visible:opacity-100 dark:bg-[#10182b]/90 dark:text-slate-500 dark:hover:bg-[#162038] dark:hover:text-red-400"
                                    >
                                        <x-heroicon-o-x-mark class="size-4" />
                                    </button>
                                    <p
                                        x-show="(threadPosts[index] || '').length > {{ (int) round($this->maxContentLength * 0.8) }}"
                                        style="display: none"
                                        :class="
                                            (threadPosts[index] || '').length >= {{ $this->maxContentLength }}
                                                ? 'text-red-500 dark:text-red-400'
                                                : 'text-slate-500 dark:text-slate-400'
                                        "
                                        class="mt-1 text-right text-xs"
                                    >
                                        <span x-text="(threadPosts[index] || '').length"></span>
                                        / {{ $this->maxContentLength }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button
                        type="button"
                        x-show="threadPosts.length < {{ $this->maxThreadPosts - 1 }} && ! $wire.isPoll"
                        style="display: none"
                        x-on:click="addPost()"
                        class="group/add mt-1 flex w-full items-center gap-3 py-1 text-left"
                    >
                        <span class="flex w-10 shrink-0 flex-col items-center">
                            <span class="w-px flex-1 bg-slate-200 dark:bg-slate-700/60"></span>
                            <span class="my-1 flex size-7 items-center justify-center rounded-full border border-dashed border-slate-300 text-slate-400 transition group-hover/add:border-pink-400 group-hover/add:bg-pink-50 group-hover/add:text-pink-500 dark:border-slate-600 dark:text-slate-500 dark:group-hover/add:border-pink-600 dark:group-hover/add:bg-pink-500/10 dark:group-hover/add:text-pink-400">
                                <x-heroicon-o-plus class="size-4" />
                            </span>
                        </span>
                        <span class="text-sm font-medium text-slate-400 transition group-hover/add:text-pink-500 dark:text-slate-500 dark:group-hover/add:text-pink-400">
                            Add another post
                        </span>
                    </button>

                    <x-input-error :messages="collect($errors->get('threadPosts.*'))->flatten()->all()" class="mt-2" />
                @endif
            </div>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 {{ $this->customDraftKey === 'post_modal' ? 'sticky bottom-0 -mx-4 bg-white/95 px-4 py-3 backdrop-blur-sm sm:-mx-6 sm:px-6 dark:bg-[#050d1b]/95' : '' }}">
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white dark:hover:bg-slate-800"
                    >
                        @if ($this->parentId)
                            {{ __('Reply') }}
                        @else
                            <span x-show="threadPosts.length === 0">{{ __('Post') }}</span>
                            <span x-show="threadPosts.length > 0" style="display: none">{{ __('Post thread') }}</span>
                        @endif
                    </button>
                    <button
                        title="Upload an image"
                        x-ref="imageButton"
                        :disabled="uploading || images.length >= uploadLimit"
                        class="flex size-10 items-center justify-center border border-slate-200/70 bg-white text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-400 dark:hover:bg-[#162038] dark:hover:text-white"
                        :class="{ 'cursor-not-allowed text-pink-500': uploading || images.length >= uploadLimit }"
                    >
                        <x-heroicon-o-photo class="h-5 w-5" />
                    </button>
                    @if (! $this->parentId && $this->isSharingUpdate)
                        <button
                            type="button"
                            x-on:click="togglePoll()"
                            :disabled="threadPosts.length > 0"
                            title="Create a poll"
                            class="flex size-10 items-center justify-center border border-slate-200/70 bg-white text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-400 dark:hover:bg-[#162038] dark:hover:text-white"
                            :class="{
                                'cursor-not-allowed opacity-40': threadPosts.length > 0,
                                'text-pink-500': isPoll,
                            }"
                        >
                            <x-heroicon-o-chart-bar class="h-5 w-5" />
                        </button>
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

        <div x-show="isPoll" class="mt-4 space-y-4" style="display: none">
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300">Poll Options</h4>
                <template x-for="(option, index) in pollOptions" :key="index">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 shrink-0 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
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
                            class="p-1 text-slate-400 transition-colors hover:text-red-500"
                            title="Remove option"
                        >
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>
                </template>

                <button
                    x-show="canAddOption()"
                    type="button"
                    x-on:click="addPollOption()"
                    class="flex items-center gap-1 text-sm text-pink-500 transition-colors hover:text-pink-600"
                >
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Add option
                </button>
            </div>

            <div>
                <label for="pollDuration" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Poll Duration
                </label>
                <select
                    id="pollDuration"
                    x-model="pollDuration"
                    class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring-pink-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-pink-600 dark:focus:ring-pink-600"
                >
                    <option value="">Select duration</option>
                    <option value="1">1 day</option>
                    <option value="2">2 days</option>
                    <option value="3">3 days</option>
                    <option value="5">5 days</option>
                    <option value="7">1 week</option>
                </select>
            </div>

            <x-input-error :messages="$errors->get('pollOptions')" class="mt-2" />
            <x-input-error :messages="$errors->get('pollDuration')" class="mt-2" />
        </div>
    </form>
</div>
