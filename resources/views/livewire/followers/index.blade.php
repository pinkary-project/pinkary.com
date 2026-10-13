<x-modal name="followers" maxWidth="2xl">
    <div class="p-4 sm:p-6" x-on:open-modal.window="$event.detail == 'followers' ? $wire.set('isOpened', true) : null">
        <div class="mb-4 border-b border-slate-200/70 pb-3 dark:border-slate-800/40">
            <h3 class="text-base font-semibold text-slate-950 dark:text-white">
                @if ($followers->isEmpty())
                    <span>@</span
                    >{{ $user->username }} does not have any followers
                @else
                    <span>@</span
                    >{{ $user->username }} followers
                @endif
            </h3>
        </div>

        @if ($followers->isNotEmpty())
            <section class="max-h-[88vh] overflow-y-auto pr-1">
                <ul class="flex flex-col gap-2">
                    @foreach ($followers as $follower)
                        <li
                            data-parent="true"
                            x-data="clickHandler"
                            x-on:click="handleNavigation($event)"
                            wire:key="follower-{{ $follower->id }}"
                        >
                            <div class="group flex items-center gap-3 rounded-xl border border-slate-200/70 bg-slate-50/80 p-3 transition-colors hover:bg-slate-100 sm:p-4 dark:border-slate-800/40 dark:bg-[#0b1324] dark:hover:bg-[#101a30]">
                                <figure class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-11 w-11 shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-11 w-11"
                                        src="{{ $follower->avatar_url }}"
                                        alt="{{ $follower->username }}"
                                    />
                                </figure>
                                <div class="flex min-w-0 flex-1 flex-col overflow-hidden text-left text-sm">
                                    <a
                                        class="flex items-center gap-1.5"
                                        href="{{ route('profile.show', ['username' => $follower->username]) }}"
                                        wire:navigate
                                        x-ref="parentLink"
                                    >
                                        <p class="truncate font-medium text-slate-950 dark:text-white">
                                            {{ $follower->name }}
                                        </p>

                                        @if ($follower->is_verified && $follower->is_company_verified)
                                            <x-icons.verified-company
                                                :color="$follower->right_color"
                                                class="size-4 shrink-0"
                                            />
                                        @elseif ($follower->is_verified)
                                            <x-icons.verified :color="$follower->right_color" class="size-4 shrink-0" />
                                        @endif
                                    </a>
                                    <p class="flex items-center truncate text-left text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                                        {{ '@'.$follower->username }}
                                        @if (auth()->user()?->isNot($user) && $follower->is_follower)
                                            <x-badge class="ml-1.5"> Follows you </x-badge>
                                        @endif
                                    </p>
                                </div>
                                <x-follow-button
                                    :id="$follower->id"
                                    :isFollower="$user->is(auth()->user()) || $follower->is_follower"
                                    :isFollowing="$follower->is_following"
                                    class="shrink-0"
                                    wire:key="follow-button-{{ $follower->id }}"
                                />
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($followers->hasPages())
                <div class="mt-4">{{ $followers->links() }}</div>
            @endif
        @endif
    </div>
</x-modal>
