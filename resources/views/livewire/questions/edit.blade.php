<div>
    <form wire:submit="update" wire:keydown.cmd.enter="update" wire:keydown.ctrl.enter="update" class="pb-0">
        <div class="min-w-0">
            <div class="group/menu relative">
                <div class="p-0">
                    <label for="{{ 'answer_question_'.$question->id }}" class="sr-only">Answer</label>

                    <x-textarea
                        id="{{ 'answer_question_'.$question->id }}"
                        wire:model="answer"
                        x-autosize
                        class="min-h-20! resize-none rounded-none! border-slate-200/70! bg-white! px-3.5! py-3! text-[0.95rem]! leading-7! text-slate-950! shadow-sm placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#10182b]! dark:text-white! dark:placeholder:text-slate-500!"
                        placeholder="Write your answer..."
                        maxlength="1000"
                        rows="3"
                        autocomplete
                    ></x-textarea>

                    <p class="mt-2 text-right text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="$wire.answer.length"></span> / 1000
                    </p>

                    @error('answer')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
            </div>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center border border-{{ $user->left_color }} px-5 py-2.5 text-sm font-semibold text-{{ $user->left_color }} transition hover:bg-slate-950 hover:text-white dark:hover:bg-slate-800"
                    >
                        {{ __('Send') }}
                    </button>

                    @if (! $question->is_reported)
                        @if (! $question->answer)
                            <button
                                wire:click.prevent="ignore"
                                wire:confirm="Are you sure you want to ignore this question?"
                                class="text-sm text-slate-400 hover:text-slate-500 focus:outline-none"
                            >
                                {{ __('Ignore') }}
                            </button>
                            <button
                                wire:click.prevent="report"
                                wire:confirm="Are you sure you want to report this question?"
                                class="text-sm text-slate-400 hover:text-red-500 focus:outline-none"
                            >
                                {{ __('Report') }}
                            </button>
                        @endif

                        @if ($question->isSharedUpdate())
                            <div
                                data-channel-picker
                                wire:ignore
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
                                                if (! this.channels.some(c => String(c.id) === String(this.selectedChannelId))) {
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
                                            if (! this.channels.some(c => String(c.id) === String(result.id))) {
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
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
