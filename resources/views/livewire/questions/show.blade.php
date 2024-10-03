<article class="block" id="q-{{ $questionId }}" x-data="copyCode">
    <div>
        <div class="flex {{ $question->isSharedUpdate() ? 'justify-end' : 'justify-between' }}">
            @unless ($question->isSharedUpdate())
                @if ($question->anonymously)
                    <div class="flex items-center gap-3 px-4 text-sm text-slate-500">
                        <div class="border-1 flex h-10 w-10 items-center justify-center rounded-full border border-dashed dark:border-slate-400 border-slate-600">
                            <span>?</span>
                        </div>

                        <p class="font-medium">Anonymously</p>
                    </div>
                @else
                    <x-avatar-with-name :user="$question->from" />
                @endif
            @endunless
            @if ($question->pinned && $pinnable)
                <div class="mb-2 flex items-center space-x-1 px-4 text-sm focus:outline-none">
                    <x-icons.pin class="h-4 w-4 dark:text-slate-400 text-slate-600" />
                    <span class="dark:text-slate-400 text-slate-600">Pinned</span>
                </div>
            @endif
        </div>

        @unless ($question->isSharedUpdate())
        <p class="mt-3 px-4 dark:text-slate-200 text-slate-800">
            {!! $question->content !!}
        </p>
        @endunless
    </div>

    @if ($question->answer)
        <div
            data-parent=true
            x-intersect.once.full="$dispatch('post-viewed', { postId: '{{ $questionId }}' })"
            x-data="clickHandler"
            x-on:click="handleNavigation($event)"
            class="group p-4 mt-3 rounded-2xl {{ $previousQuestionId === $questionId ? 'dark:bg-slate-700/60 bg-slate-200/60' : 'border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50' }}
            {{ $commenting ?: "cursor-pointer transition-colors duration-100 ease-in-out dark:hover:bg-slate-700/60 hover:bg-slate-100/60" }}"
        >
            <div class="flex justify-between">
                <a
                    href="{{ route('profile.show', ['username' => $question->to->username]) }}"
                    class="group/profile flex items-center gap-3"
                    data-navigate-ignore="true"
                    wire:navigate
                >
                    <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-100 transition-opacity group-hover/profile:opacity-90">
                        <img
                            src="{{ $question->to->avatar_url }}"
                            alt="{{ $question->to->username }}"
                            class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                        />
                    </figure>
                    <div class="overflow-hidden text-sm">
                        <div class="items flex">
                            <p class="truncate font-medium dark:text-slate-50 text-slate-950">
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

                        <p class="truncate text-slate-500 transition-colors dark:group-hover/profile:text-slate-400 group-hover/profile:text-slate-600">
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
                                class="inline-flex items-center rounded-md border border-transparent py-1 text-sm dark:text-slate-400 text-slate-600 transition duration-150 ease-in-out dark:hover:text-slate-50 hover:text-slate-950 focus:outline-none">
                                <x-heroicon-o-ellipsis-horizontal class="h-6 w-6" />
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
                   class="truncate text-xs text-slate-500 transition-colors dark:hover:text-slate-400 hover:text-slate-600"
                >
                    In response to {{ '@'.$question->parent->to->username }}
                </a>
            @endif

            <div x-data="showMore">
                <div
                    class="mt-3 break-words dark:text-slate-200 text-slate-800 overflow-hidden answer"
                    wire:ignore.self
                    x-ref="parentDiv"
                >
                    <p x-data="hasLightBoxImages">
                        {!! $question->answer !!}
                    </p>
                </div>

                <div x-show="showMore === true" class="mt-1 answer">
                    <button
                        data-navigate-ignore="true"
                        @click="showButtonAction"
                        class="text-sm text-pink-500 flex ml-auto"
                        x-text="showMoreButtonText"
                    ></button>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                <div class="flex items-center gap-1">
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
                            "flex items-center transition-colors group-hover:text-pink-500 dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none",
                            "cursor-pointer" => ! $commenting,
                        ])
                    >
                        <x-heroicon-o-chat-bubble-left-right class="size-4" />
                        @if ($question->children_count > 0)
                            <span class="ml-1">
                                {{ Number::abbreviate($question->children_count) }}
                            </span>
                        @endif
                    </a>

                    <span>•</span>

                    @php
                        $likeExists = $question->is_liked;
                        $likesCount = $question->likes_count;
                    @endphp

                    <button
                        x-data="likeButton('{{ $question->id }}', @js($likeExists), {{ $likesCount }}, @js(auth()->check()))"
                        x-cloak
                        data-navigate-ignore="true"
                        x-on:click="toggleLike"
                        :title="likeButtonTitle"
                        class="flex items-center transition-colors dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <x-heroicon-s-heart class="h-4 w-4" x-show="isLiked" />
                        <x-heroicon-o-heart class="h-4 w-4" x-show="!isLiked" />
                        <span class="ml-1" x-show="count" x-text="likeButtonText"></span>
                    </button>
                    <span>•</span>
                    <p
                        class="inline-flex cursor-help items-center"
                        title="{{ Number::format($question->views) }} {{ str('View')->plural($question->views) }}"
                    >
                        <x-icons.chart class="h-4 w-4"/>
                        @if ($question->views > 0)
                            <span class="mx-1">
                                {{ Number::abbreviate($question->views) }}
                            </span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center text-slate-500 ">
                    @php
                        $timestamp = $question->answer_updated_at ?: $question->answer_created_at
                    @endphp

                    <time
                        class="cursor-help"
                        title="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                        datetime="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                    >
                        {{  $question->answer_updated_at ? 'Edited:' : null }}
                        {{
                            $timestamp->timezone(session()->get('timezone', 'UTC'))
                                ->diffForHumans(short: true)
                        }}
                    </time>

                    <span class="mx-1">•</span>

                    <button
                        data-navigate-ignore="true"
                        x-data="bookmarkButton('{{ $question->id }}', @js($question->is_bookmarked), {{ $question->bookmarks_count }}, @js(auth()->check()))"
                        x-cloak
                        x-on:click="toggleBookmark"
                        :title="bookmarkButtonTitle"
                        class="mr-1 flex items-center transition-colors dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <x-heroicon-s-bookmark class="h-4 w-4" x-show="isBookmarked" />
                        <x-heroicon-o-bookmark class="h-4 w-4" x-show="!isBookmarked" />
                        <span class="ml-1" x-show="count" x-text="bookmarkButtonText"></span>
                    </button>
                    <x-dropdown align="left"
                                width=""
                                dropdown-classes="top-[-3.4rem] shadow-none"
                                content-classes="flex flex-col space-y-1"
                    >
                        <x-slot name="trigger">
                            <button
                                data-navigate-ignore="true"
                                x-bind:class="{ 'text-pink-500 hover:text-pink-600': open,
                                                'text-slate-500 dark:hover:text-slate-400 hover:text-slate-600': !open }"
                                title="Share"
                                class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none"
                            >
                                <x-heroicon-o-paper-airplane class="h-4 w-4" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
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
                                class="text-slate-500 transition-colors dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none"
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
                                class="text-slate-500 transition-colors dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none"
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
                                        question: '{{ str_replace("'", "\'", $question->isSharedUpdate() ? $question->answer : $question->content) }}',
                                        message: '{{ $question->isSharedUpdate() ? 'See it on Pinkary' : 'See response on Pinkary' }}',
                                    })
                                "
                                type="button"
                                class="text-slate-500 transition-colors dark:hover:text-slate-400 hover:text-slate-600 focus:outline-none"
                            >
                                <x-icons.twitter-x class="size-4" />
                            </button>
                        </x-slot>
                    </x-dropdown>
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

    @if($commenting && $inThread && (auth()->id() !== $question->to_id || ! is_null($question->answer)))
        <livewire:questions.create :parent-id="$questionId" :to-id="auth()->id()" />
    @endif
</article>
