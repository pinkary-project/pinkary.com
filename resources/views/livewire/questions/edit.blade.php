<div>
    <form wire:submit="update">
        <div class="mt-4 flex items-center justify-between">
            <div class="w-full">
                <div class="mb-1">
                    <label for="{{ 'answer_question_'.$question->id }}" class="sr-only">Answer</label>

                    <x-textarea
                        id="{{ 'answer_question_'.$question->id }}"
                        wire:model="answer"
                        x-autosize
                        class="h-24 w-full resize-none border-none border-transparent bg-transparent text-black focus:border-transparent focus:ring-0 focus:outline-0 dark:text-white"
                        placeholder="Write your answer..."
                        maxlength="1000"
                        rows="3"
                        autocomplete
                    ></x-textarea>

                    <p class="text-right text-xs text-slate-400"><span x-text="$wire.answer.length"></span> / 1000</p>

                    @error('answer')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-primary-colorless-button
                            class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                            type="submit"
                        >
                            {{ __('Save') }}
                        </x-primary-colorless-button>

                        @if (! $question->parent_id)
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
                                    class="inline-flex h-9 items-center gap-1.5 border border-slate-200/70 bg-white px-2.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 focus:outline-none dark:border-slate-800/30 dark:bg-[#10182b] dark:text-slate-300 dark:hover:bg-[#162038] dark:hover:text-white"
                                    :class="{ 'border-pink-500! text-pink-600! dark:text-pink-400!': $wire.topicId }"
                                    title="Topic"
                                >
                                    <x-heroicon-o-tag class="size-3.5" />
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
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (! $question->is_reported)
                            @if (! $question->answer)
                                <button
                                    wire:click.prevent="ignore"
                                    wire:confirm="Are you sure you want to ignore this question?"
                                    class="text-slate-400 hover:text-slate-500 focus:outline-none"
                                >
                                    Ignore
                                </button>
                                <button
                                    wire:click.prevent="report"
                                    wire:confirm="Are you sure you want to report this question?"
                                    class="text-slate-400 hover:text-red-500 focus:outline-none"
                                >
                                    Report
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
