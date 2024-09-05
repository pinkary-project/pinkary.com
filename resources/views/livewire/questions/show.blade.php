<article class="block" id="q-{{ $questionId }}" x-data="copyCode">
    @unless ($inTrending || $pinnable || ($inThread && ! $commenting))
        @php
            $parentQuestion = $question->parent;

            do {
                $parentQuestions[] = $parentQuestion;
            } while ($parentQuestion = $parentQuestion?->parent);
        @endphp

        @php $parentQuestions = collect($parentQuestions)->filter()->reverse(); @endphp

        @foreach($parentQuestions as $parentQuestion)
        <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="false" />
            <div class="relative h-6 -mb-3">
                <span class="absolute left-8 h-full w-1.5 rounded-full bg-slate-700" aria-hidden="true"></span>
            </div>
        @endforeach
    @endunless
    <div>
        <div class="flex {{ $question->isSharedUpdate() ? 'justify-end' : 'justify-between' }}">
            @unless ($question->isSharedUpdate())
                @if ($question->anonymously)
                    <div class="flex items-center gap-3 px-4 text-sm text-slate-500">
                        <div class="flex items-center justify-center w-10 h-10 border border-dashed rounded-full border-1 border-slate-400">
                            <span>?</span>
                        </div>

                        <p class="font-medium">Anonymously</p>
                    </div>
                @else
                    <x-avatar-with-name :user="$question->from" />
                @endif
            @endunless
            @if ($question->pinned && $pinnable)
                <div class="flex items-center px-4 mb-2 space-x-1 text-sm focus:outline-none">
                    <x-icons.pin class="w-4 h-4 text-slate-400" />
                    <span class="text-slate-400">Pinned</span>
                </div>
            @endif
        </div>

        @unless ($question->isSharedUpdate())
        <p class="px-4 mt-3 text-slate-200">
            {!! $question->content !!}
        </p>
        @endunless
    </div>

    @if ($question->answer)
        <div
            data-parent=true
            x-data="clickHandler"
            x-on:click="handleNavigation($event)"
            class="group p-4 sm:mt-3 sm:rounded-2xl {{ $previousQuestionId === $questionId ? 'bg-slate-700/60' : 'bg-slate-900' }}
            {{ $commenting ?: "cursor-pointer transition-colors duration-100 ease-in-out hover:bg-slate-700/60" }}"
        >
            <div class="flex justify-between">
                <a
                    href="{{ route('profile.show', ['username' => $question->to->username]) }}"
                    class="flex items-center gap-3 group/profile"
                    data-navigate-ignore="true"
                    wire:navigate
                >
                    <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover/profile:opacity-90">
                        <img
                            src="{{ $question->to->avatar_url }}"
                            alt="{{ $question->to->username }}"
                            class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                        />
                    </figure>
                    <div class="overflow-hidden text-sm">
                        <div class="flex items">
                            <p class="font-medium truncate text-slate-50">
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

                        <p class="truncate transition-colors text-slate-500 group-hover/profile:text-slate-400">
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
                                class="inline-flex items-center py-1 text-sm transition duration-150 ease-in-out border border-transparent rounded-md text-slate-400 hover:text-slate-50 focus:outline-none">
                                <x-heroicon-o-ellipsis-horizontal class="w-6 h-6" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (! $question->pinned && auth()->user()->can('pin', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="pin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="w-4 h-4 text-slate-50" />
                                    <span>Pin</span>
                                </x-dropdown-button>
                            @elseif ($question->pinned)
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="unpin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="w-4 h-4" />
                                    <span>Unpin</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && $question->answer_created_at?->diffInHours() < 24 && auth()->user()->can('update', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    x-on:click="$dispatch('open-modal', 'question.edit.answer.{{ $questionId }}')"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-m-pencil class="w-4 h-4"/>
                                    <span>Edit</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && auth()->user()->can('ignore', $question))
                                <x-dropdown-button
                                    data-navigate-ignore="true"
                                    wire:click="ignore"
                                    wire:confirm="Are you sure you want to delete this question?"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-o-trash class="w-4 h-4" />
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
                   class="text-xs truncate transition-colors text-slate-500 hover:text-slate-400"
                >
                    In response to {{ '@'.$question->parent->to->username }}
                </a>
            @endif

            <div x-data="showMore">
                <div
                    class="mt-3 overflow-hidden break-words text-slate-200 answer"
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
                        class="flex ml-auto text-sm text-pink-500"
                        x-text="showMoreButtonText"
                    ></button>
                </div>
            </div>

            <div class="flex items-center justify-between mt-3 text-sm text-slate-500">
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
                            "flex items-center transition-colors group-hover:text-pink-500 hover:text-slate-400 focus:outline-none",
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
                        data-navigate-ignore="true"
                        @if ($likeExists)
                            wire:click="unlike"
                        @else
                            wire:click="like"
                        @endif
                        x-data="particlesEffect"
                        x-on:click="executeParticlesEffect($event)"
                        title="{{ Number::format($likesCount) }} {{ str('like')->plural($likesCount) }}"
                        class="flex items-center transition-colors hover:text-slate-400 focus:outline-none"
                    >
                        @if ($likeExists)
                            <x-heroicon-s-heart class="w-4 h-4"/>
                        @else
                            <x-heroicon-o-heart class="w-4 h-4"/>
                        @endif
                        @if ($likesCount)
                            <span class="ml-1">
                                {{ Number::abbreviate($likesCount) }}
                            </span>
                        @endif
                    </button>
                    <span>•</span>
                    <p
                        class="inline-flex items-center cursor-help"
                        title="{{ Number::format($question->views) }} {{ str('View')->plural($question->views) }}"
                    >
                        <x-icons.chart class="w-4 h-4"/>
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
                        @if ($question->is_bookmarked)
                            wire:click="unbookmark()"
                        @else
                            wire:click="bookmark()"
                        @endif
                        title="{{ Number::format($question->bookmarks_count) }} {{ str('bookmark')->plural($question->bookmarks_count) }}"
                        class="flex items-center mr-1 transition-colors hover:text-slate-400 focus:outline-none"
                    >
                        @if ($question->is_bookmarked)
                            <x-heroicon-s-bookmark class="w-4 h-4" />
                        @else
                            <x-heroicon-o-bookmark class="w-4 h-4" />
                        @endif
                        @if ($question->bookmarks_count)
                            <span class="ml-1">
                                {{ Number::abbreviate($question->bookmarks_count) }}
                            </span>
                        @endif
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
                                                'text-slate-500 hover:text-slate-400': !open }"
                                title="Share"
                                class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none"
                            >
                                <x-heroicon-o-paper-airplane class="w-4 h-4" />
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
                                class="transition-colors text-slate-500 hover:text-slate-400 focus:outline-none"
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
                                class="transition-colors text-slate-500 hover:text-slate-400 focus:outline-none"
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
                                class="transition-colors text-slate-500 hover:text-slate-400 focus:outline-none"
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
                    <h2 class="text-lg font-medium text-slate-50">Edit Answer</h2>
                    <livewire:questions.edit
                        :questionId="$question->id"
                        :key="'edit-answer-'.$question->id"
                    />
                </div>
            </x-modal>
        @endif
    @elseif (auth()->user()?->is($user))
        <livewire:questions.edit
            :questionId="$question->id"
            :key="$question->id"
        />
    @endif

    @if($commenting && $inThread && (auth()->id() !== $question->to_id || ! is_null($question->answer)))
        <livewire:questions.create :parent-id="$questionId" :to-id="auth()->id()" />
    @endif

    @if($inThread && $question->children->isNotEmpty())
        <div class="pl-3">
            @foreach($question->children as $comment)
                @break($loop->depth > 5)

                <livewire:questions.show :question-id="$comment->id" :$inThread :wire:key="$comment->id" />
            @endforeach
        </div>
    @endif
</article>
