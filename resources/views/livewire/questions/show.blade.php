<article class="block">
    @if (! empty($question->content))
        <div>
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
                                <svg aria-label="Verified" class="text-{{ $question->from->right_color }} ml-1 mt-0.5 flex-shrink-0 fill-current saturate-200" height="15" role="img" viewBox="0 0 40 40" width="18"><title>Verified</title><path d="M19.998 3.094 14.638 0l-2.972 5.15H5.432v6.354L0 14.64 3.094 20 0 25.359l5.432 3.137v5.905h5.975L14.638 40l5.36-3.094L25.358 40l3.232-5.6h6.162v-6.01L40 25.359 36.905 20 40 14.641l-5.248-3.03v-6.46h-6.419L25.358 0l-5.36 3.094Zm7.415 11.225 2.254 2.287-11.43 11.5-6.835-6.93 2.244-2.258 4.587 4.581 9.18-9.18Z" fill-rule="evenodd" ></path></svg>
                            @endif
                        </div>

                        <p class="truncate text-slate-500 transition-colors hover:text-slate-400">
                            {{ '@'.$question->from->username }}
                        </p>
                    </div>
                </a>
            @endif

            <p class="mb-4 mt-3 px-4 text-slate-200">
                {!! $question->content !!}
            </p>
        </div>
    @endif

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
                            <svg aria-label="Verified" class="text-{{ $question->to->right_color }} ml-1 mt-0.5 flex-shrink-0 fill-current saturate-200" height="15" role="img" viewBox="0 0 40 40" width="18"><title>Verified</title><path d="M19.998 3.094 14.638 0l-2.972 5.15H5.432v6.354L0 14.64 3.094 20 0 25.359l5.432 3.137v5.905h5.975L14.638 40l5.36-3.094L25.358 40l3.232-5.6h6.162v-6.01L40 25.359 36.905 20 40 14.641l-5.248-3.03v-6.46h-6.419L25.358 0l-5.36 3.094Zm7.415 11.225 2.254 2.287-11.43 11.5-6.835-6.93 2.244-2.258 4.587 4.581 9.18-9.18Z" fill-rule="evenodd" ></path></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 transition-colors hover:text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 transition-colors hover:text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                    </button>
                </div>
            </div>
        </div>
    @elseif (auth()->user()?->is($user))
        <livewire:questions.edit :questionId="$question->id" :key="$question->id" />
    @endif
</article>
