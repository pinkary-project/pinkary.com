<div class="mb-12 w-full dark:text-slate-200 text-slate-400">
    <div class="mb-8 w-full max-w-md">
        <div class="relative flex items-center py-1">

            <x-heroicon-o-magnifying-glass class="absolute left-5 z-50 size-5"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users and content..."
                class="w-full mx-1 !rounded-2xl dark:!bg-slate-950 !bg-slate-50 !bg-opacity-80 py-3 pl-14"
            />
        </div>
    </div>

    @if ($results->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">No matching users or content found.</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($results as $result)
                    @if ($result instanceof App\Models\Question)
                        @php($question = $result)
                        <li>
                            <article class="block">
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
                                    <div class="answer mt-3 rounded-2xl bg-slate-900 p-4">
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

                                        <p class="mt-3 break-words text-slate-200">
                                            {!! $question->answer !!}
                                        </p>

                                        @php($likeExists = $question->likes->contains('user_id', auth()->id()))

                                        <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                                            <div class="flex items-center">
                                                <button
                                                    @if ($likeExists)
                                                        wire:click="unlike()"
                                                    @else
                                                        wire:click="like()"
                                                    @endif
                                                    x-data="particlesEffect"
                                                    x-on:click="executeParticlesEffect($event)"

                                                    class="flex items-center transition-colors hover:text-slate-400 focus:outline-none"
                                                >
                                                    @if ($likeExists)
                                                        <x-icons.heart-solid class="h-4 w-4" />
                                                    @else
                                                        <x-icons.heart class="h-4 w-4" />
                                                    @endif

                                                    @php($likesCount = $question->likes_count)
                                                    @if ($likesCount)
                                                        <p
                                                            class="cursor-click ml-1"
                                                            title="{{ Number::format($likesCount) }} {{ str('like')->plural($likesCount) }}"
                                                        >
                                                            {{ Number::abbreviate($likesCount) }} {{ str('like')->plural($likesCount) }}
                                                        </p>
                                                    @endif
                                                </button>
                                                @if ($question->views > 0)
                                                    <span class="mx-1">•</span>
                                                    <x-icons.chart class="h-4 w-4" />
                                                    <p
                                                        class="ml-1 cursor-help"
                                                        title="{{ Number::format($question->views) }} {{ str('view')->plural($question->views) }}"
                                                    >
                                                        {{ Number::abbreviate($question->views) }} {{ str('view')->plural($question->views) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="flex items-center text-slate-500">
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
                            </article>
                        </li>
                    @elseif ($result instanceof App\Models\User)
                        @php($user = $result)
                        <li
                            data-parent=true
                            x-data="clickHandler"
                            x-on:click="handleNavigation($event)"
                            wire:key="user-{{ $user->id }}"
                        >
                            <div class="group flex items-center gap-3 rounded-2xl border dark:border-slate-900 border-slate-200 dark:bg-slate-950 bg-slate-50 dark:bg-opacity-80 p-4 transition-colors dark:hover:bg-slate-900 hover:bg-slate-100">
                                <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                        src="{{ $user->avatar_url }}"
                                        alt="{{ $user->username }}"
                                    />
                                </figure>
                                <div class="flex flex-col overflow-hidden text-sm text-left">
                                    <a
                                        class="flex items-center space-x-2"
                                        href="{{ route('profile.show', ['username' => $user->username]) }}"
                                        wire:navigate
                                        x-ref="parentLink"
                                    >
                                        <p class="text-wrap truncate font-medium dark:text-white text-black">
                                            {{ $user->name }}
                                        </p>

                                        @if ($user->is_verified && $user->is_company_verified)
                                            <x-icons.verified-company
                                                :color="$user->right_color"
                                                class="size-4"
                                            />
                                        @elseif ($user->is_verified)
                                            <x-icons.verified
                                                :color="$user->right_color"
                                                class="size-4"
                                            />
                                        @endif
                                    </a>
                                    <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                                        {{ '@'.$user->username }}
                                    </p>
                                </div>
                                <x-follow-button
                                    :id="$user->id"
                                    :isFollower="auth()->check() && $user->is_follower"
                                    :isFollowing="auth()->check() && $user->is_following"
                                    class="ml-auto"
                                    wire:key="follow-button-{{ $user->id }}"
                                />
                        </div>
                    </li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif
</div>
