<article class="block" id="q-{{ $questionId }}" x-data="copyCode">
    @unless ($question->isSharedUpdate())
        <div class="border-b border-slate-200 p-6 dark:border-white/5 xl:px-8 xl:pt-8">
            <div class="flex {{ $question->isSharedUpdate() ? 'justify-end' : 'justify-between' }}">
                @unless ($question->isSharedUpdate())
                    @if ($question->anonymously)
                        <div class="flex items-center gap-3 pr-4 text-sm text-slate-500 font-medium">
                            <div class="flex size-10 items-center justify-center rounded-full border border-dashed border-slate-600 border-1 dark:border-slate-400">?</div>
                            <span>Anonymously</span>
                        </div>
                    @else
                        <x-avatar-with-name :user="$question->from"/>
                    @endif
                @endunless
                @if ($question->pinned && $pinnable)
                    <div class="mb-2 flex items-center px-4 text-sm space-x-1 focus:outline-none">
                        <x-icons.pin class="h-4 w-4 text-slate-600 dark:text-slate-400"/>
                        <span class="text-slate-600 dark:text-slate-400">Pinned</span>
                    </div>
                @endif
            </div>

            @unless ($question->isSharedUpdate())
                <p class="mt-3 text-slate-800 dark:text-slate-200">
                    {!! $question->content !!}
                </p>
            @endunless
        </div>
    @endunless

    @if ($question->answer)
        <div class="p-6 xl:p-8"
             data-parent=true
             x-intersect.once.full="$dispatch('post-viewed', { postId: '{{ $questionId }}' })"
             x-data="clickHandler"
             x-on:click="handleNavigation($event)"
        >
            <div class="flex justify-between">
                <x-avatar-with-name :user="$question->to"/>
                @if (auth()->check() && auth()->user()->can('update', $question))
                    <x-dropdown
                        align="right"
                        width="48"
                    >
                        <x-slot name="trigger">
                            <button
                                data-navigate-ignore="true"
                                class="inline-flex items-center rounded-md border border-transparent py-1 text-sm text-slate-600 transition duration-150 ease-in-out hover:text-slate-950 focus:outline-none dark:text-slate-400 dark:hover:text-slate-50">
                                <x-heroicon-o-ellipsis-horizontal class="size-6"/>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (! $question->pinned && auth()->user()->can('pin', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="pin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4"/>
                                    <span>Pin</span>
                                </x-dropdown-button>
                            @elseif ($question->pinned)
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="unpin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4"/>
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
                                    <x-heroicon-o-trash class="h-4 w-4"/>
                                    <span>Delete</span>
                                </x-dropdown-button>
                            @endif
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>

            <div class="mt-3 overflow-hidden break-words text-sm text-gray-200">
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
                       class="truncate text-xs text-slate-500 transition-colors hover:text-slate-600 dark:hover:text-slate-400"
                    >
                        In response to {{ '@'.$question->parent->to->username }}
                    </a>
                @endif

                <div x-data="showMore">
                    <div
                        class="mt-3 overflow-hidden break-words text-slate-800 answer dark:text-slate-200"
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
                            class="ml-auto flex text-sm text-pink-500"
                            x-text="showMoreButtonText"
                        ></button>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-2">
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
                        "flex items-center transition-colors group-hover:text-pink-500 dark:hover:text-gray-400 hover:text-slate-600 focus:outline-none",
                            "cursor-pointer" => ! $commenting,
                        ])
                    >
                        <x-icons.chat-bubble class="size-4"/>
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
                        class="flex items-center transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-400"
                    >
                        <x-heroicon-s-heart class="size-4" x-show="isLiked"/>
                        <x-heroicon-o-heart class="size-4" x-show="!isLiked"/>
                        <span class="mx-1 text-xs" x-show="count" x-text="likeButtonText"></span>
                    </button>

                    <span>•</span>

                    <div class="inline-flex cursor-help items-center"
                         title="{{ Number::format($question->views) }} {{ str('View')->plural($question->views) }}"
                    >
                        <x-icons.chart class="size-4"/>
                        @if ($question->views > 0)
                            <span class="mx-1 text-xs">{{ Number::abbreviate($question->views) }}</span>
                        @endif
                    </div>

                    <span>•</span>

                    <button
                        data-navigate-ignore="true"
                        class="flex items-center text-gray-500 transition-colors duration-150 ease-in-out hover:text-slate-600 focus:outline-none dark:hover:text-gray-400"
                        x-data="bookmarkButton('{{ $question->id }}', @js($question->is_bookmarked), {{ $question->bookmarks_count }}, @js(auth()->check()))"
                        x-cloak
                        x-on:click="toggleBookmark"
                        :title="bookmarkButtonTitle"
                    >
                        <x-heroicon-s-bookmark class="h-4 w-4" x-show="isBookmarked"/>
                        <x-heroicon-o-bookmark class="h-4 w-4" x-show="!isBookmarked"/>
                        <span class="mx-1 text-xs" x-show="count" x-text="bookmarkButtonText"></span>
                    </button>

                    <span>•</span>

                    <button
                        class="flex items-center text-gray-500 transition-colors duration-150 ease-in-out hover:text-gray-400 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4"
                             fill="none">
                            <path
                                d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z"
                                stroke="currentColor" stroke-width="1.5"></path>
                            <path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>

                        <span class="mx-1 text-xs">1</span>
                    </button>
                </div>

                <div class="flex items-center gap-2 text-gray-500">
                    @php
                        $timestamp = $question->answer_updated_at ?: $question->answer_created_at;
                    @endphp
                    <time
                        class="cursor-help text-xs"
                        title="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                        datetime="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                    >
                        {{  $question->answer_updated_at ? 'Edited:' : null }}
                        {{
                            $timestamp->timezone(session()->get('timezone', 'UTC'))
                                ->diffForHumans(short: true)
                        }}
                    </time>

                    <!--
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
                                <x-heroicon-o-paper-airplane class="h-4 w-4"/>
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
                                class="text-slate-500 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-400"
                            >
                                <x-heroicon-o-link class="size-4"/>
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
                                class="text-slate-500 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-400"
                            >
                                <x-heroicon-o-link class="size-4"/>
                            </button>
                            @php
                                $sharableQuestion = str_replace("'", "\'", $question->isSharedUpdate() ? $question->answer : $question->content);
                                $link = null;

                                if (preg_match('/<div\s+id="link-preview-card"[^>]*>(.*)<\/div>(?!.*<\/div>)/si', $sharableQuestion, $matches)) {
                                    $linkPreviewCard = $matches[0];

                                    if (preg_match('/data-url="([^"]*)"/', $linkPreviewCard, $urlMatches)) {
                                        $link = " {$urlMatches[1]} ";
                                    }
                                }

                                $sharable = $link ? str_replace($linkPreviewCard, $link, $sharableQuestion) : $sharableQuestion;
                            @endphp
                            <button
                                data-navigate-ignore="true"
                                x-cloak
                                x-data="shareProfile"
                                x-on:click="
                                    twitter({
                                        url: '{{ route('questions.show', ['username' => $question->to->username, 'question' => $question]) }}',
                                        question: '{{ $sharable }}',
                                        message: '{{ $question->isSharedUpdate() ? 'See it on Pinkary' : 'See response on Pinkary' }}',
                                    })
                                "
                                type="button"
                                class="text-slate-500 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-400"
                            >
                                <x-icons.twitter-x class="size-4"/>
                            </button>
                        </x-slot>
                    </x-dropdown>
                    -->
                </div>
            </div>
        </div>

        @if (! $question->is_ignored && $question->answer_created_at?->diffInHours() < 24 && auth()->user()?->can('update', $question))
            <x-modal
                max-width="md"
                name="question.edit.answer.{{ $questionId }}"
            >
                <div class="p-8">
                    <h2 class="text-lg font-medium text-slate-950 dark:text-slate-50">Edit Answer</h2>
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
                <h2 class="text-lg font-medium text-slate-950 dark:text-slate-50">Delete Question</h2>
                <div class="mt-4 text-slate-500 dark:text-slate-400">
                    <p>Are you sure you want to delete this question?</p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <x-secondary-button
                        x-on:click="$dispatch('close-modal', 'question.delete.{{ $questionId }}.confirmation')"
                    >
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-primary-button
                        wire:click="ignore"
                    >
                        {{ __('Delete') }}
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
        <div class="border-t border-white/5">
            @auth
                <livewire:questions.create :parent-id="$questionId" :to-id="auth()->id()"/>
            @else
                <div class="p-6 xl:p-8">
                    <p class="text-center text-slate dark:text-slate-400">
                        You must be logged in to comment on this question.
                    </p>
                </div>
            @endauth
        </div>
    @endif
</article>
