<div class="mb-12 w-full dark:text-slate-200 text-slate-400">
    <div class="mb-8 w-full max-w-md">
        <div class="relative flex items-center py-1">

            <x-heroicon-o-magnifying-glass class="absolute left-5 z-50 size-5"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full mx-1 !rounded-2xl dark:!bg-slate-950 !bg-slate-50 !bg-opacity-80 py-3 pl-14"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">No users found.</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($users as $user)
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
                @endforeach
            </ul>
        </section>
    @endif
</div>
