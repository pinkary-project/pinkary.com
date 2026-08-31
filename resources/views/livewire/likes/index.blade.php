<x-modal name="likes-{{ $question->id }}" maxWidth="2xl">
    <div
        class="p-4 sm:p-6"
        x-on:open-modal.window="$event.detail == 'likes-{{ $question->id }}' ? $wire.set('isOpened', true) : null"
    >
        <div class="mb-4 border-b border-slate-200/70 pb-3 dark:border-slate-800/40">
            <h3 class="text-base font-semibold text-slate-950 dark:text-white">
                @if ($users->isEmpty())
                    {{ __('No one has liked this post yet') }}
                @else
                    {{ __('Liked by') }}
                @endif
            </h3>
        </div>

        @if ($users->isNotEmpty())
            <section class="max-h-[88vh] overflow-y-auto pr-1">
                <ul class="flex flex-col gap-2">
                    @foreach ($users as $user)
                        <li
                            data-parent="true"
                            x-data="clickHandler"
                            x-on:click="handleNavigation($event)"
                            wire:key="user-{{ $user->id }}"
                        >
                            <div class="group flex items-center gap-3 rounded-xl border border-slate-200/70 bg-slate-50/80 p-3 transition-colors hover:bg-slate-100 sm:p-4 dark:border-slate-800/40 dark:bg-[#0b1324] dark:hover:bg-[#101a30]">
                                <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-11 w-11 shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-11 w-11"
                                        src="{{ $user->avatar_url }}"
                                        alt="{{ $user->username }}"
                                    />
                                </figure>
                                <div class="flex min-w-0 flex-1 flex-col overflow-hidden text-left text-sm">
                                    <a
                                        class="flex items-center gap-1.5"
                                        href="{{ route('profile.show', ['username' => $user->username]) }}"
                                        wire:navigate
                                        x-ref="parentLink"
                                    >
                                        <p class="truncate font-medium text-slate-950 dark:text-white">
                                            {{ $user->name }}
                                        </p>

                                        @if ($user->is_verified && $user->is_company_verified)
                                            <x-icons.verified-company
                                                :color="$user->right_color"
                                                class="size-4 shrink-0"
                                            />
                                        @elseif ($user->is_verified)
                                            <x-icons.verified :color="$user->right_color" class="size-4 shrink-0" />
                                        @endif
                                    </a>
                                    <p class="flex items-center truncate text-left text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                                        {{ '@'.$user->username }}
                                        @if ($user->hasAttribute('is_follower') && $user->is_follower)
                                            <x-badge class="ml-1.5"> Follows you </x-badge>
                                        @endif
                                    </p>
                                </div>
                                <x-follow-button
                                    :id="$user->id"
                                    :isFollower="$user->hasAttribute('is_follower') && $user->is_follower"
                                    :isFollowing="$user->hasAttribute('is_following') && $user->is_following"
                                    class="shrink-0"
                                    wire:key="follow-button-{{ $user->id }}"
                                />
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($users->hasPages())
                <div class="mt-4">{{ $users->links() }}</div>
            @endif
        @endif
    </div>
</x-modal>
