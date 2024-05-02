<article class="block">
    <div class="rounded-2xl bg-slate-800 bg-opacity-20 border border-slate-800 hover:border hover:border-slate-600
        hover:bg-clip-padding hover:backdrop-filter p-4">
        <div class="flex justify-between">
            <a
                href="{{ route('profile.show', ['username' => $comment->owner->username]) }}"
                class="flex items-center gap-3 group"
                wire:navigate
            >
                <figure
                    class="{{ $comment->owner->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
                    <img
                        src="{{ $comment->owner->avatar_url }}"
                        alt="{{ $comment->owner->username }}"
                        class="{{ $comment->owner->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                    />
                </figure>
                <div class="overflow-hidden text-sm">
                    <div class="flex items">
                        <p class="truncate font-medium text-slate-50">
                            {{ $comment->owner->name }}
                        </p>

                        @if ($comment->owner->is_verified && $comment->owner->is_company_verified)
                            <x-icons.verified-company
                                :color="$comment->owner->right_color"
                                class="ml-1 mt-0.5 h-3.5 w-3.5"
                            />
                        @elseif ($comment->owner->is_verified)
                            <x-icons.verified
                                :color="$comment->owner->right_color"
                                class="ml-1 mt-0.5 h-3.5 w-3.5"
                            />
                        @endif
                    </div>

                    <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                        {{ '@'.$comment->owner->username }}
                    </p>
                </div>
            </a>
            @if (auth()->check())
                <x-dropdown
                    align="right"
                    width="48"
                >
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center rounded-md border border-transparent py-1 text-sm text-slate-400 transition duration-150 ease-in-out hover:text-slate-50 focus:outline-none">
                            <x-icons.ellipsis-horizontal class="h-6 w-6"/>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @can('update', $comment)
                            <x-dropdown-button
                                class="flex items-center gap-1.5"
                                x-on:click="$dispatch('open-modal', 'comment.edit.{{ $comment->id }}')"
                            >
                                <x-heroicon-m-pencil class="h-4 w-4"/>
                                {{ __('Edit') }}
                            </x-dropdown-button>
                        @endif
                        @can('delete', $comment)
                            <x-dropdown-button
                                class="flex text-slate-400 items-center gap-1.5"
                                x-on:click="$dispatch('open-modal', 'comment.delete.{{ $comment->id }}')"
                            >
                                <x-heroicon-m-trash class="h-4  w-4"/>
                                {{ __('Delete') }}
                            </x-dropdown-button>
                        @endif
                    </x-slot>
                </x-dropdown>
            @endif
        </div>

        <p class="mt-3 break-words text-slate-200">
            {!! $comment->content !!}
        </p>

        <div class="mt-3 flex items-center justify-end text-sm text-slate-500">
            <div class="flex items-center text-slate-500">
                <time
                    class="cursor-help"
                    title="{{ $comment->updated_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                    datetime="{{ $comment->updated_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                >
                    {{ $comment->updated_at > $comment->created_at ? 'Edited' : 'Posted' }}
                    {{
                        $comment->updated_at
                            ->timezone(session()->get('timezone', 'UTC'))
                            ->diffForHumans()
                    }}
                </time>
            </div>
        </div>
    </div>
    @can('update', $comment)
        <livewire:comments.edit :commentId="$comment->id" :key="$comment->id" />
    @endcan
    @can('delete', $comment)
        <livewire:comments.delete :commentId="$comment->id" :key="$comment->id" />
    @endcan
</article>
