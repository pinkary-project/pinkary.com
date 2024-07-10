<article class="block" id="q-{{ $questionId }}">
    <div>
        <div class="flex {{ $question->isSharedUpdate() ? 'justify-end' : 'justify-between' }}">
            @unless ($question->isSharedUpdate())
                @if ($question->anonymously)
                    <div class="flex items-center gap-3 px-4 text-sm text-slate-500">
                        <div class="border-1 flex h-10 w-10 items-center justify-center rounded-full border border-dashed border-slate-400">
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
                    <x-icons.pin class="h-4 w-4 text-slate-400" />
                    <span class="text-slate-400">Pinned</span>
                </div>
            @endif
        </div>

        @unless ($question->isSharedUpdate())
        <p class="mb-4 mt-3 px-4 text-slate-200">
            {!! $question->content !!}
        </p>
        @endunless
    </div>

    @if ($question->answer)
        <div class="answer mt-3 rounded-2xl {{ $previousQuestionId === $questionId ? 'bg-slate-700/60' : 'bg-slate-900' }} p-4">
            <div class="flex justify-between">
                <a
                    href="{{ route('profile.show', ['username' => $question->to->username]) }}"
                    class="group flex items-center gap-3"
                    wire:navigate
                >
                    <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
                        <img
                            src="{{ $question->to->avatar_url }}"
                            alt="{{ $question->to->username }}"
                            class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                        />
                    </figure>
                    <div class="overflow-hidden text-sm">
                        <div class="items flex">
                            <p class="truncate font-medium text-slate-50">
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

                        <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
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
                            <button class="inline-flex items-center rounded-md border border-transparent py-1 text-sm text-slate-400 transition duration-150 ease-in-out hover:text-slate-50 focus:outline-none">
                                <x-icons.ellipsis-horizontal class="h-6 w-6" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (! $question->pinned && auth()->user()->can('pin', $question))
                                <x-dropdown-button
                                    wire:click="pin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4 text-slate-50" />
                                    <span>Pin</span>
                                </x-dropdown-button>
                            @elseif ($question->pinned)
                                <x-dropdown-button
                                    wire:click="unpin"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.pin class="h-4 w-4" />
                                    <span>Unpin</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && $question->answer_created_at?->diffInHours() < 24 && auth()->user()->can('update', $question))
                                <x-dropdown-button
                                    x-on:click="$dispatch('open-modal', 'question.edit.answer.{{ $questionId }}')"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-heroicon-m-pencil class="h-4 w-4"/>
                                    <span>Edit</span>
                                </x-dropdown-button>
                            @endif
                            @if (! $question->is_ignored && auth()->user()->can('ignore', $question))
                                <x-dropdown-button
                                    wire:click="ignore"
                                    wire:confirm="Are you sure you want to delete this question?"
                                    class="flex items-center gap-1.5"
                                >
                                    <x-icons.trash class="h-4 w-4" />
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
                   wire:navigate
                   class="truncate text-xs text-slate-500 transition-colors hover:text-slate-400"
                >
                    In response to {{ '@'.$question->parent->to->username }}
                </a>
            @endif

            <div x-data="showMore">
                <div
                    class="mt-3 break-words text-slate-200 overflow-hidden"
                    wire:ignore.self
                    x-ref="parentDiv"
                >
                    <p>
                        {!! $question->answer !!}
                    </p>
                </div>

                <div x-show="showMore === true" class="mt-1">
                    <button
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
                            href="{{Route('questions.show', [
                                'question' => $question->id,
                                'username' => $question->to->username,
                            ])}}"
                            wire:navigate
                        @endif
                        title="{{ Number::format($question->children_count) }} {{ str('Comment')->plural($question->children_count) }}"
                        @class([
                            "flex items-center transition-colors hover:text-slate-400 focus:outline-none",
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
                        $likeExists = $question->likes->contains('user_id', auth()->id());
                        $likesCount = $question->likes_count;
                    @endphp

                    <button
                        @if ($likeExists)
                            wire:click="unlike()"
                        @else
                            wire:click="like()"
                        @endif
                        x-data="particlesEffect"
                        x-on:click="executeParticlesEffect($event)"
                        title="{{ Number::format($likesCount) }} {{ str('like')->plural($likesCount) }}"
                        class="flex items-center transition-colors hover:text-slate-400 focus:outline-none"
                    >
                        @if ($likeExists)
                            <x-icons.heart-solid class="h-4 w-4"/>
                        @else
                            <x-icons.heart class="h-4 w-4"/>
                        @endif
                        @if ($likesCount)
                            <span class="ml-1">
                                {{ Number::abbreviate($likesCount) }}
                            </span>
                        @endif
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
                    @php($timestamp = $question->answer_updated_at ?: $question->answer_created_at)
                    <time
                        class="cursor-help"
                        title="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                        datetime="{{ $timestamp->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                    >
                        {{  $question->answer_updated_at ? 'Edited:' : null }}
                        {{
                            $timestamp->timezone(session()->get('timezone', 'UTC'))
                                ->diffForHumans()
                        }}
                    </time>

                    <span class="mx-1">•</span>
                    <x-dropdown align="left"
                                width="48"
                                dropdown-classes="top-[-3.4rem] shadow-none"
                                content-classes="flex flex-col space-y-1"
                    >
                        <x-slot name="trigger">
                            <button
                                x-bind:class="{ 'text-pink-500 hover:text-pink-600': open,
                                                'text-slate-500 hover:text-slate-400': !open }"
                                title="Share"
                                class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none"
                            >
                                <x-icons.paper-airplane class="h-4 w-4" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <button
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
                                class="text-slate-500 transition-colors hover:text-slate-400 focus:outline-none"
                            >
                                <x-icons.link class="size-4" />
                            </button>
                            <button
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
                                class="text-slate-500 transition-colors hover:text-slate-400 focus:outline-none"
                            >
                                <x-icons.link class="size-4" />
                            </button>
                            <button
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
                                class="text-slate-500 transition-colors hover:text-slate-400 focus:outline-none"
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

