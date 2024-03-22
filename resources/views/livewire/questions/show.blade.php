<article class="block">
    <div>
        <div class="flex justify-between">
            @if ($question->anonymously)
                <div class="flex items-center gap-3 px-4 text-sm text-slate-500">
                    <div class="border-1 flex h-10 w-10 items-center justify-center rounded-full border border-dashed border-slate-400">
                        <span>?</span>
                    </div>

                    <p class="font-medium">Anonymously</p>
                </div>
            @else
                <a
                    href="{{ route('profile.show', ['user' => $question->from->username]) }}"
                    class="flex items-center gap-3 px-4"
                    wire:navigate
                >
                    <figure class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-800 transition-opacity hover:opacity-90">
                        <img
                            src="{{ $question->from->avatar ? url($question->from->avatar) : $question->from->avatar_url }}"
                            alt="{{ $question->from->username }}"
                            class="h-10 w-10 rounded-full"
                        />
                    </figure>

                    <div class="overflow-hidden text-sm">
                        <div class="flex">
                            <p class="truncate font-medium text-slate-50">
                                {{ $question->from->name }}
                            </p>
                            @if ($question->from->is_verified)
                                <x-icons.verified
                                    :color="$question->from->right_color"
                                    class="ml-1 mt-0.5 h-4 w-4 flex-shrink-0"
                                />
                            @endif
                        </div>

                        <p class="truncate text-slate-500 transition-colors hover:text-slate-400">
                            {{ '@'.$question->from->username }}
                        </p>
                    </div>
                </a>
            @endif

            @if (auth()->check() && auth()->user()->can('update', $question))
                @if (! $question->pinned)
                    <button
                        wire:click="pin"
                        class="group flex items-center space-x-2 px-2 text-slate-50 transition-colors hover:text-slate-400 focus:outline-none"
                    >
                        <x-icons.pin class="h-4 w-4 text-slate-50 group-hover:text-slate-400" />
                        <span class="group-hover:text-slate-400">Pin</span>
                    </button>
                @elseif ($question->pinned)
                    <button
                        class="group flex items-center space-x-2 px-2 text-slate-50 transition-colors hover:text-slate-400 focus:outline-none"
                        wire:click="unpin"
                    >
                        <x-icons.pin class="h-4 w-4 text-slate-50 group-hover:text-slate-400" />
                        <span class="group-hover:text-slate-400">Unpin</span>
                    </button>
                @endif
            @endif
        </div>

        <p class="mb-4 mt-3 px-4 text-slate-200">
            {!! $question->content !!}
        </p>
    </div>

    @if ($question->answer)
        <div class="answer mt-3 rounded-2xl bg-slate-900 p-4">
            <a
                href="{{ route('profile.show', ['user' => $question->to->username]) }}"
                class="flex items-center gap-3"
                wire:navigate
            >
                <figure class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-800 transition-opacity hover:opacity-90">
                    <img
                        src="{{ $question->to->avatar ? url($question->to->avatar) : $question->to->avatar_url }}"
                        alt="{{ $question->to->username }}"
                        class="h-10 w-10 rounded-full"
                    />
                </figure>
                <div class="overflow-hidden text-sm">
                    <div class="items flex">
                        <p class="truncate font-medium text-slate-50">
                            {{ $question->to->name }}
                        </p>
                        @if ($question->to->is_verified)
                            <x-icons.verified
                                :color="$question->to->right_color"
                                class="ml-1 mt-0.5 h-4 w-4 flex-shrink-0"
                            />
                        @endif
                    </div>

                    <p class="truncate text-slate-500 transition-colors hover:text-slate-400">
                        {{ '@'.$question->to->username }}
                    </p>
                </div>
            </a>

            <p class="mt-3 text-slate-200">
                {!! $question->answer !!}
            </p>

            @php
                $likeExists = $question
                    ->likes()
                    ->where('user_id', auth()->id())
                    ->exists();
            @endphp

            <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                <div class="flex items-center">
                    <button
                        @if ($likeExists)
                            wire:click="unlike()"
                        @else
                            wire:click="like()"
                        @endif
                        class="flex items-center transition-colors hover:text-slate-400 focus:outline-none"
                    >
                        @if ($question->likes()->where('user_id', auth()->id())->exists())
                            <x-icons.heart-solid class="h-4 w-4" />
                        @else
                            <x-icons.heart class="h-4 w-4" />
                        @endif

                        <p class="ml-1">
                            {{ $question->likes()->count() ? $question->likes()->count().' '.str('like')->plural($question->likes()->count()) : '' }}
                        </p>
                    </button>
                </div>
                <div class="flex items-center text-slate-500">
                    <time datetime="{{ $question->answered_at->timezone(auth()->user()?->timezone ?: 'UTC')->toIso8601String() }}">
                        {{
                            $question->answered_at
                                ->timezone(auth()->user()?->timezone ?: 'UTC')
                                ->diffForHumans()
                        }}
                    </time>
                    <span class="mx-1">•</span>
                    <button
                        x-data="shareProfile"
                        x-show="isVisible"
                        @click="share({
                                        url: '{{
                                            route('questions.show', [
                                                'user' => $question->to->username,
                                                'question' => $question,
                                            ])
                                        }}'
                                    })"
                        class="text-slate-500 transition-colors hover:underline focus:outline-none"
                    >
                        <x-icons.paper-airplane class="h-4 w-4 transition-colors hover:text-slate-400" />
                    </button>
                    <button
                        x-cloak
                        x-data="copyUrl"
                        x-show="isVisible"
                        @click="copyToClipboard('{{
                            route('questions.show', [
                                'user' => $question->to->username,
                                'question' => $question,
                            ])
                        }}')"
                        type="button"
                        class="text-slate-500 transition-colors hover:underline focus:outline-none"
                    >
                        <x-icons.paper-airplane class="h-4 w-4 transition-colors hover:text-slate-400" />
                    </button>
                </div>
            </div>
        </div>
    @elseif (auth()->user()?->is($user))
        <livewire:questions.edit :questionId="$question->id" :key="$question->id" />
    @endif
</article>
