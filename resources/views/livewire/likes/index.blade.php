<x-modal
    name="likes-{{ $question->id }}"
    maxWidth="2xl"
>
    <div class="p-4 md:p-10" x-on:open-modal.window="$event.detail == 'likes-{{ $question->id }}' ? $wire.set('isOpened', true) : null">
        <div>
            @if ($users->isEmpty())
                <strong>No one has liked this post yet</strong>
            @else
                <strong>Liked by</strong>
            @endif
        </div>

        @if ($users->isNotEmpty())
            <section class="mt-10 max-w-2xl max-h-96 overflow-y-auto">
                <ul class="flex flex-col gap-2">
                    @foreach ($users as $user)
                        <li
                            data-parent=true
                            x-data="clickHandler"
                            x-on:click="handleNavigation($event)"
                            wire:key="user-{{ $user->id }}"
                        >
                            <div class="group flex items-center gap-3 rounded-2xl border dark:border-slate-900 border-slate-200 dark:bg-slate-950 bg-slate-100 bg-opacity-80 p-4 transition-colors dark:hover:bg-slate-900 hover:bg-slate-200">
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
                                    <p class="truncate text-left text-slate-500 transition-colors group-hover:text-slate-400">
                                        {{ '@'.$user->username }}
                                        @if ($user->hasAttribute('is_follower') && $user->is_follower)
                                            <x-badge class="ml-1">
                                                Follows you
                                            </x-badge>
                                        @endif
                                    </p>
                                </div>
                                <x-follow-button
                                    :id="$user->id"
                                    :isFollower="$question->to->is(auth()->user()) || ($user->hasAttribute('is_follower') && $user->is_follower)"
                                    :isFollowing="$user->hasAttribute('is_following') && $user->is_following"
                                    class="ml-auto"
                                    wire:key="follow-button-{{ $user->id }}"
                                />
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="mt-5">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-modal>
