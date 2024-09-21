<x-modal
    name="followers"
    maxWidth="2xl"
>
    <div class="p-4 md:p-10" x-on:open-modal.window="$event.detail == 'followers' ? $wire.set('isOpened', true) : null">
        <div>
            @if ($followers->isEmpty())
                <strong> <span>@</span>{{ $user->username }} does not have any followers </strong>
            @else
                <strong> <span>@</span>{{ $user->username }} followers </strong>
            @endif
        </div>

        @if ($followers->isNotEmpty())
            <section class="mt-10 max-w-2xl max-h-96 overflow-y-auto">
                <ul class="flex flex-col gap-2">
                    @foreach ($followers as $follower)
                        <li
                            data-parent=true
                            x-data="clickHandler"
                            x-on:click="handleNavigation($event)"
                            wire:key="follower-{{ $follower->id }}"
                        >
                            <div class="group flex items-center gap-3 rounded-2xl border dark:border-slate-900 border-slate-200 dark:bg-slate-950 bg-slate-100 bg-opacity-80 p-4 transition-colors dark:hover:bg-slate-900 hover:bg-slate-200">
                                <figure class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                        src="{{ $follower->avatar_url }}"
                                        alt="{{ $follower->username }}"
                                    />
                                </figure>
                                <div class="flex flex-col overflow-hidden text-sm text-left">
                                    <a
                                        class="flex items-center space-x-2"
                                        href="{{ route('profile.show', ['username' => $follower->username]) }}"
                                        wire:navigate
                                        x-ref="parentLink"
                                    >
                                        <p class="text-wrap truncate font-medium">
                                            {{ $follower->name }}
                                        </p>

                                        @if ($follower->is_verified && $follower->is_company_verified)
                                            <x-icons.verified-company
                                                :color="$follower->right_color"
                                                class="size-4"
                                            />
                                        @elseif ($follower->is_verified)
                                            <x-icons.verified
                                                :color="$follower->right_color"
                                                class="size-4"
                                            />
                                        @endif
                                    </a>
                                    <p class="truncate text-left text-slate-500 transition-colors group-hover:text-slate-400">
                                        {{ '@'.$follower->username }}
                                        @if (auth()->user()?->isNot($user) && $follower->is_follower)
                                            <x-badge class="ml-1">
                                                Follows you
                                            </x-badge>
                                        @endif
                                    </p>
                                </div>
                                <x-follow-button
                                    :id="$follower->id"
                                    :isFollower="$user->is(auth()->user()) || $follower->is_follower"
                                    :isFollowing="$follower->is_following"
                                    class="ml-auto"
                                    wire:key="follow-button-{{ $follower->id }}"
                                />
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="mt-5">
                {{ $followers->links() }}
            </div>
        @endif
    </div>
</x-modal>
