<article class="block space-y-3" id="q-{{ $questionId }}" x-data="copyCode">
    @php
        $chipClasses = 'inline-flex items-center gap-1.5 border border-slate-200/70 bg-slate-50 px-2.5 py-1.5 text-[0.72rem] font-medium text-slate-500 dark:border-slate-800/30 dark:bg-[#0b1324] dark:text-slate-400';
        $interactiveChipClasses = $chipClasses.' transition hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#1a2440] dark:hover:text-white';
        $menuButtonClasses = 'inline-flex items-center border border-slate-200/70 bg-slate-50 px-2.5 py-1.5 text-sm text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-950 focus:outline-none dark:border-slate-800/30 dark:bg-[#0b1324] dark:text-slate-400 dark:hover:bg-[#1a2440] dark:hover:text-white';
        $shareMenuContentClasses = 'flex flex-col space-y-1 rounded-2xl border border-slate-200/80 bg-white/95 p-2 text-slate-500 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-white/10 dark:bg-gray-900/95 dark:text-slate-300 dark:shadow-black/30';
        $shareMenuItemClasses = 'rounded-xl px-3 py-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-950 focus:outline-none dark:text-slate-400 dark:hover:bg-gray-800 dark:hover:text-white';
    @endphp
    <div class="space-y-2.5">
        <div class="flex items-start {{ $question->isSharedUpdate() ? 'justify-end' : 'justify-between gap-3' }}">
            @unless ($question->isSharedUpdate())
                @if ($question->anonymously)
                    <div class="inline-flex items-center gap-3 border border-dashed border-slate-300/80 bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/30 dark:bg-[#0b1324] dark:text-slate-400">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-dashed border-slate-300 dark:border-slate-700">
                            <span>?</span>
                        </div>

                        <p class="font-medium">Anonymously</p>
                    </div>
                @else
                    <div class="min-w-0">
                        <x-avatar-with-name :user="$question->from" />
                    </div>
                @endif

                <div class="flex items-center gap-2 self-start">
                    <a
                        class="{{ $interactiveChipClasses }}"
                        href="{{ 'https://translate.google.com/?sl=auto&tl=en&text='.urlencode($question->sharable_content) }}"
                        target="_blank"
                        data-navigate-ignore="true"
                    >
                        <x-heroicon-o-language class="h-4 w-4"/>
                        <span class="hidden sm:inline">Translate</span>
                    </a>

                    @if ($question->pinned && $pinnable)
                        <div class="{{ $chipClasses }}">
                            <x-icons.pin class="h-4 w-4" />
                            <span>Pinned</span>
                        </div>
                    @endif
                </div>
            @else
                @if ($question->pinned && $pinnable)
                    <div class="{{ $chipClasses }}">
                        <x-icons.pin class="h-4 w-4" />
                        <span>Pinned</span>
                    </div>
                @endif
            @endunless
        </div>

        @unless ($question->isSharedUpdate())
            <div class="px-0 py-0.5">
                <p class="text-sm leading-7 text-slate-700 dark:text-slate-200 sm:text-[0.95rem]">
                    {!! $question->content !!}
                </p>
            </div>
        @endunless
    </div>

    @if ($question->answer)
        @php
            $actionMetricClasses = 'inline-flex items-center gap-1.5 text-[0.82rem] text-slate-500 transition-colors';
            $actionMetricHoverClasses = 'hover:text-slate-700 dark:hover:text-slate-200';
            $actionSeparatorClasses = 'h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-700/80';
        @endphp
        <div
            data-parent=true
            x-intersect.once.full="$dispatch('post-viewed', { postId: '{{ $questionId }}' })"
            x-data="clickHandler"
            x-on:click="handleNavigation($event)"
            class="group px-0 py-0 {{ $previousQuestionId === $questionId ? 'bg-pink-50 dark:bg-[#151225]' : '' }}
            {{ $commenting ?: 'cursor-pointer transition-colors duration-100 ease-in-out' }}"
        >
            <div class="flex items-start justify-between gap-3">
                <a
                    href="{{ route('profile.show', ['username' => $question->to->username]) }}"
                    class="group/profile flex items-center gap-3"
                    data-navigate-ignore="true"
                    wire:navigate
                >
                    <figure class="{{ $question->to->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-12 w-12 flex-shrink-0 border border-slate-200/70 bg-slate-100 transition-opacity group-hover/profile:opacity-90 dark:border-slate-800/30 dark:bg-[#10182b]">
                        <img
                            src="{{ $question->to->avatar_url }}"
                            alt="{{ $question->to->username }}"
                            class="{{ $question->to->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-12 w-12"
                        />
                    </figure>
                    <div class="min-w-0 overflow-hidden text-sm">
                        <div class="flex items-center">
                            <p class="truncate font-medium text-slate-950 dark:text-white">
                                {{ $question->to->name }}
                            </p>

                            @if ($question->to->is_verified && $question->to->is_company_verified)
                                <x-icons.verified-company
                                    :color="$question->to->right_color"
                                    class="ml-1 mt-0.5 h-3.5 w-3.5"
                                />
                            @elseif ($question->to->is_verified)
                                <x-icons.verified
                                    :color="$question->to->right_color"
                                    class="ml-1 mt-0.5 h-3.5 w-3.5"
                                />
                            @endif
                        </div>

                        <p class="mt-1 truncate text-slate-500 transition-colors group-hover/profile:text-slate-600 dark:text-slate-400 dark:group-hover/profile:text-slate-300">
                            {{ '@'.$question->to->username }}
                        </p>
                    </div>
                </a>

                @if (auth()->check() && auth()->user()->can('update', $question))
                    <x-dropdown
                        align="right"
                        width="48"
                    >
                        <x-slot name="trigger">
                            <button
                                data-navigate-ignore="true"
                                class="{{ $menuButtonClasses }}">
                                <x-heroicon-o-ellipsis-horizontal class="h-5 w-5" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (! $question->pinned && auth()->user()->can('pin', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="pin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4" />
                                    <span>Pin</span>
                                </x-dropdown-button>
                            @elseif ($question->pinned)
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="unpin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4" />
                                    <span>Unpin</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && $question->answer_created_at?->diffInHours() < 24 && auth()->user()->can('update', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    x-on:click="$dispatch('open-modal', 'question.edit.answer.{{ $questionId }}')"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-m-pencil class="h-4 w-4"/>
                                    <span>Edit</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && auth()->user()->can('ignore', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    x-on:click="$dispatch('open-modal', 'question.delete.{{ $questionId }}.confirmation')"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                    <span>Delete</span>
                                </x-dropdown-button>
                            @endif
                            @if (auth()->user()->can('viewLikes', $question) && $question->likes_count > 0)
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    x-on:click="$dispatch('open-modal', 'likes-{{ $question->id }}')"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-s-heart class="h-4 w-4" />
                                    <span>View likes</span>
                                </x-dropdown-button>
                            @endif
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>

            @if((! $inThread || $commenting) && $question->parent)
                <a href="{{
                        route('questions.show', [
                            'username' => $question->parent->to->username,
                            'question' => $question->parent,
                            'previousQuestionId' => $questionId,
                        ])
                    }}"
                   data-navigate-ignore="true"
                   wire:navigate
                             class="mt-3 inline-flex max-w-full items-center gap-2 border border-slate-200/70 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-[#0b1324] dark:text-slate-400 dark:hover:bg-[#1a2440] dark:hover:text-white"
                >
                    <x-heroicon-o-arrow-turn-down-right class="h-3.5 w-3.5" />
                    In response to {{ '@'.$question->parent->to->username }}
                </a>
            @endif

            <div x-data="showMore">
                <div
                        class="mt-3 overflow-hidden break-words text-slate-700 dark:text-slate-200 answer"
                    wire:ignore.self
                    x-ref="parentDiv"
                >
                    <p x-data="hasLightBoxImages">
                        {!! $question->answer !!}
                    </p>
                </div>

                <div x-show="showMore === true" class="mt-2 answer">
                    <button
                        data-navigate-ignore="true"
                        @click="showButtonAction"
                        class="ml-auto flex text-sm font-medium text-pink-500"
                        x-text="showMoreButtonText"
                    ></button>
                </div>
            </div>

            @if ($question->isPoll())
                <livewire:questions.poll-voting :questionId="$question->id" :key="'poll-'.$question->id" />
            @endif

            <div class="mt-3 flex items-end justify-between gap-3 border-t border-slate-200/70 pt-2.5 dark:border-slate-800/30">
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-x-3 gap-y-2">
                    <a
                        @if (! $commenting)
                            x-ref="parentLink"
                            href="{{Route('questions.show', [
                                'question' => $question->id,
                                'username' => $question->to->username,
                            ])}}"
                            wire:navigate
                        @endif
                        title="{{ Number::format($question->children_count) }} {{ str('Comment')->plural($question->children_count) }}"
                        @class([
                            "$actionMetricClasses $actionMetricHoverClasses focus:outline-none",
                            "cursor-pointer" => ! $commenting,
                        ])
                    >
                        <x-heroicon-o-chat-bubble-left-right class="size-4" />
                        @if ($question->children_count > 0)
                            <span>
                                {{ Number::abbreviate($question->children_count) }}
                            </span>
                        @endif
                    </a>

                    <span aria-hidden="true" class="{{ $actionSeparatorClasses }}"></span>

                    @php
                        $likeExists = $question->is_liked;
                        $likesCount = $question->likes_count;
                    @endphp

                    <button
                        x-data="likeButton('{{ $question->id }}', @js(auth()->check()))"
                        x-cloak
                        data-is-liked="@js($likeExists)"
                        data-likes-count="{{ $likesCount }}"
                        data-navigate-ignore="true"
                        x-on:click="toggleLike"
                        :title="likeButtonTitle"
                        class="{{ $actionMetricClasses }} {{ $actionMetricHoverClasses }} focus:outline-none"
                    >
                        <x-heroicon-s-heart class="h-4 w-4" x-show="isLiked" />
                        <x-heroicon-o-heart class="h-4 w-4" x-show="!isLiked" />
                        <span x-show="count" x-text="likeButtonText"></span>
                    </button>

                    <span aria-hidden="true" class="{{ $actionSeparatorClasses }}"></span>

                    <p
                        class="{{ $actionMetricClasses }} cursor-help"
                        title="{{ Number::format($question->views) }} {{ str('View')->plural($question->views) }}"
                    >
                        <x-icons.chart class="h-4 w-4"/>
                        @if ($question->views > 0)
                            <span>
                                {{ Number::abbreviate($question->views) }}
                            </span>
                        @endif
                    </p>

                    <span aria-hidden="true" class="{{ $actionSeparatorClasses }}"></span>

                    <button
                        data-navigate-ignore="true"
                        x-data="bookmarkButton('{{ $question->id }}', @js(auth()->check()))"
                        data-is-bookmarked="@js($question->is_bookmarked)"
                        data-bookmarks-count="{{ $question->bookmarks_count }}"
                        x-cloak
                        x-on:click="toggleBookmark"
                        :title="bookmarkButtonTitle"
                        class="{{ $actionMetricClasses }} {{ $actionMetricHoverClasses }} focus:outline-none"
                    >
                        <x-heroicon-s-bookmark class="h-4 w-4" x-show="isBookmarked" />
                        <x-heroicon-o-bookmark class="h-4 w-4" x-show="!isBookmarked" />
                        <span x-show="count" x-text="bookmarkButtonText"></span>
                    </button>

                    <span aria-hidden="true" class="{{ $actionSeparatorClasses }}"></span>

                    @php
                        $timestamp = $question->answer_updated_at ?: $question->answer_created_at
                    @endphp

                    <x-dropdown align="left"
                                width=""
                                dropdown-classes="top-[-3.8rem] shadow-none"
                                :content-classes="$shareMenuContentClasses"
                    >
                        <x-slot name="trigger">
                            <button
                                data-navigate-ignore="true"
                                x-bind:class="{ 'text-pink-500': open,
                                                'text-slate-500 hover:text-slate-700 dark:hover:text-slate-200': !open }"
                                title="Share"
                                class="inline-flex items-center text-[0.82rem] transition-colors duration-150 ease-in-out focus:outline-none"
                            >
                                <x-heroicon-o-paper-airplane class="h-4 w-4" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <a
                                data-navigate-ignore="true"
                                href="{{ 'https://translate.google.com/?sl=auto&tl=en&text='.urlencode($question->sharable_answer) }}"
                                target="_blank"
                                title="Translate"
                                class="{{ $shareMenuItemClasses }}"
                            >
                                <x-heroicon-o-language class="size-4" />
                            </a>
                            <button
                                data-navigate-ignore="true"
                                x-cloak
                                x-data="copyUrl"
                                x-show="isVisible"
                                x-on:click="
                                    copyToClipboard(
                                        '{{
                                            route('questions.show', [
                                                'username' => $question->to->username,
                                                'question' => $question,
                                            ])
                                        }}',
                                    )
                                "
                                type="button"
                                class="{{ $shareMenuItemClasses }}"
                            >
                                <x-heroicon-o-link class="size-4" />
                            </button>
                            <button
                                data-navigate-ignore="true"
                                x-cloak
                                x-data="shareProfile"
                                x-show="isVisible"
                                x-on:click="
                                    share({
                                        url: '{{
                                            route('questions.show', [
                                                'username' => $question->to->username,
                                                'question' => $question,
                                            ])
                                        }}',
                                    })
                                "
                                class="{{ $shareMenuItemClasses }}"
                            >
                                <x-heroicon-o-link class="size-4" />
                            </button>
                            <button
                                data-navigate-ignore="true"
                                x-cloak
                                x-data="shareProfile"
                                x-on:click="
                                    twitter({
                                        url: '{{ route('questions.show', ['username' => $question->to->username, 'question' => $question]) }}',
                                        question: '{{ $question->isSharedUpdate() ? $question->sharable_answer : $question->sharable_content }}',
                                        message: '{{ $question->isSharedUpdate() ? 'See it on Pinkary' : 'See response on Pinkary' }}',
                                    })
                                "
                                type="button"
                                class="{{ $shareMenuItemClasses }}"
                            >
                                <x-icons.twitter-x class="size-4" />
                            </button>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="flex flex-shrink-0 items-center gap-2 text-[0.82rem] text-slate-500">
                    <time
                        class="inline-flex cursor-help items-center whitespace-nowrap"
                        title="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                        datetime="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                    >
                        {{ $question->answer_updated_at ? 'Edited: ' : null }}
                        {{
                            $timestamp->timezone(session()->get('timezone', 'UTC'))
                                ->diffForHumans(short: true)
                        }}
                    </time>
                </div>
            </div>
        </div>
        @if (! $question->is_ignored && $question->answer_created_at?->diffInHours() < 24 && auth()->user()?->can('update', $question))
            <x-modal
                max-width="md"
                name="question.edit.answer.{{ $questionId }}"
            >
                <div class="p-8">
                    <h2 class="text-lg font-medium dark:text-slate-50 text-slate-950">Edit Answer</h2>
                    <livewire:questions.edit
                        :questionId="$question->id"
                        :key="'edit-answer-'.$question->id"
                    />
                </div>
            </x-modal>
        @endif

        <x-modal
            max-width="md"
            name="question.delete.{{ $questionId }}.confirmation"
        >
            <div class="p-8">
                <h2 class="text-lg font-medium dark:text-slate-50 text-slate-950">Delete Question</h2>
                <div class="mt-4 text-slate-500 dark:text-slate-400">
                    <p>Are you sure you want to delete this question?</p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <x-secondary-button
                        x-on:click="$dispatch('close-modal', 'question.delete.{{ $questionId }}.confirmation')"
                    >
                        Cancel
                    </x-secondary-button>
                    <x-primary-button
                        wire:click="ignore"
                    >
                        Delete
                    </x-primary-button>
                </div>
            </div>
        </x-modal>

    @elseif (auth()->user()?->is($user))
        <livewire:questions.edit
            :questionId="$question->id"
            :key="'edit-'.$question->id"
        />
    @endif

    @if (auth()->user()?->can('viewLikes', $question))
        <livewire:likes.index
            :questionId="$question->id"
            :key="'likes-'.$question->id"
        />
    @endif

    @if($commenting && $inThread && (auth()->id() !== $question->to_id || ! is_null($question->answer)))
        <livewire:questions.create :parent-id="$questionId" :to-id="auth()->id()" />
    @endif
</article>
