<div class="" id="questions-create">
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

                    <p class="mt-2 text-right text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="$wire.content.length"></span> / {{ $this->maxContentLength }}
                    </p>
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
            </div>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white dark:hover:bg-slate-800"
                    >
                        {{ $this->parentId ? __('Reply') : __('Post') }}
                    </button>
                    @if (! $this->parentId)
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                topics: @js($topics->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug])),
                                recents: @js($recentTopics->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug])),
                                get selectedTopic() {
                                    return this.topics.find(t => t.id == $wire.topicId);
                                },
                                get filteredTopics() {
                                    if (! this.search.trim()) return this.topics;
                                    return this.topics.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase().trim()));
                                },
                                get hasExactMatch() {
                                    return this.topics.some(t => t.name.toLowerCase() === this.search.toLowerCase().trim());
                                },
                                selectTopic(topic) {
                                    $wire.set('topicId', topic.id);
                                    this.open = false;
                                    this.search = '';
                                },
                                createTopic() {
                                    if (! this.search.trim()) return;
                                    $wire.selectOrCreateTopic(this.search.trim()).then(() => {
                                        this.open = false;
                                        this.search = '';
                                    });
                                }
                            }"
                            class="relative inline-block text-left"
                        >
                            <button
                                type="button"
                                @click="open = ! open"
                                class="inline-flex h-10 items-center gap-1.5 border border-slate-200/70 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 focus:outline-none dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038] dark:hover:text-white"
                                :class="{ 'border-pink-500! text-pink-600! dark:text-pink-400!': $wire.topicId }"
                                title="Select Topic"
                            >
                                <x-heroicon-o-tag class="size-4" />
                                <span x-text="selectedTopic ? selectedTopic.name : 'Select Topic'"></span>
                                <x-heroicon-m-chevron-down class="size-3 text-slate-400" />
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute left-0 z-50 mt-1 w-60 rounded-md border border-slate-200/80 bg-white p-1.5 shadow-lg shadow-slate-900/10 focus:outline-none dark:border-slate-800/80 dark:bg-[#0b1324] dark:shadow-black/30"
                                style="display: none"
                            >
                                <div class="p-1">
                                    <input
                                        x-model="search"
                                        type="text"
                                        placeholder="Search or create topic..."
                                        class="w-full border-b border-slate-200 bg-transparent px-2 py-1 text-xs text-slate-900 placeholder:text-slate-400 focus:border-pink-500 focus:ring-0 focus:outline-none dark:border-slate-700/50 dark:text-white dark:placeholder:text-slate-500"
                                        @keydown.escape="open = false"
                                        @keydown.enter.prevent="createTopic()"
                                    />
                                </div>
                                <div class="max-h-52 overflow-y-auto py-1">
                                    <template x-if="search.trim() !== '' && ! hasExactMatch">
                                        <button
                                            type="button"
                                            @click="createTopic()"
                                            class="flex w-full items-center gap-1.5 rounded px-2.5 py-1.5 text-left text-xs font-semibold text-pink-600 transition hover:bg-pink-50 dark:text-pink-400 dark:hover:bg-[#16203a]"
                                        >
                                            <x-heroicon-m-plus class="size-3.5" />
                                            <span>Create "#<span x-text="search.trim()"></span>"</span>
                                        </button>
                                    </template>

                                    <template x-if="! search.trim() && recents.length > 0">
                                        <div>
                                            <div class="px-2.5 py-1 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                                Recent Topics
                                            </div>
                                            <template x-for="topic in recents" :key="'recent-' + topic.id">
                                                <button
                                                    type="button"
                                                    @click="selectTopic(topic)"
                                                    class="flex w-full items-center justify-between px-2.5 py-1.5 text-left text-xs text-slate-700 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-[#16203a] dark:hover:text-white"
                                                    :class="{
                                                        'font-semibold text-pink-600 dark:text-pink-400':
                                                            $wire.topicId == topic.id,
                                                    }"
                                                >
                                                    <span x-text="topic.name"></span>
                                                    <x-heroicon-m-check
                                                        x-show="$wire.topicId == topic.id"
                                                        class="size-3.5 text-pink-500"
                                                    />
                                                </button>
                                            </template>
                                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                                        </div>
                                    </template>

                                    <template x-if="! search.trim() && topics.length > 0">
                                        <div class="px-2.5 py-1 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                            All Topics
                                        </div>
                                    </template>

                                    <template x-for="topic in filteredTopics" :key="topic.id">
                                        <button
                                            type="button"
                                            @click="selectTopic(topic)"
                                            class="flex w-full items-center justify-between px-2.5 py-1.5 text-left text-xs text-slate-700 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-[#16203a] dark:hover:text-white"
                                            :class="{
                                                'font-semibold text-pink-600 dark:text-pink-400':
                                                    $wire.topicId == topic.id,
                                            }"
                                        >
                                            <span x-text="topic.name"></span>
                                            <x-heroicon-m-check
                                                x-show="$wire.topicId == topic.id"
                                                class="size-3.5 text-pink-500"
                                            />
                                        </button>
                                    </template>
                                    <div
                                        x-show="filteredTopics.length === 0 && (! search.trim() || hasExactMatch)"
                                        class="px-2.5 py-2 text-xs text-slate-400"
                                    >
                                        No topics found
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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
                            title="Create a poll"
                            class="flex size-10 items-center justify-center border border-slate-200/70 bg-white text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-400 dark:hover:bg-[#162038] dark:hover:text-white"
                            :class="{ 'text-pink-500': isPoll }"
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
            @error('topicId')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
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
        </div>
    </form>
</div>
