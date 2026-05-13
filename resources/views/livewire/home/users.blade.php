<div class="w-full text-gray-200">
    <div class="border-b border-r border-white/5 bg-black/10 p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">People</p>
                <h3 class="mt-1 text-xl font-semibold text-white">Find the next profile to follow.</h3>
            </div>

            <p class="max-w-sm text-sm leading-6 text-gray-400">
                Search by name or username, or browse the current discovery list powered by existing user data.
            </p>
        </div>

        <div class="relative mt-4 flex items-center">
            <x-heroicon-o-magnifying-glass class="absolute left-4 z-10 size-5 text-slate-500"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full !rounded-none !border-white/5 !bg-gray-800/30 !py-3 !pl-12 !text-white shadow-none placeholder:!text-gray-500 dark:!border-white/5 dark:!bg-gray-800/30 dark:placeholder:!text-gray-500"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="border-b border-r border-dashed border-white/10 bg-black/10 p-6 text-center">
            <p class="text-lg font-medium text-white">No users found.</p>
            <p class="mt-2 text-sm text-slate-400">Try another search or clear the query to return to the discovery list.</p>
        </section>
    @else
        <section>
            <ul class="divide-y divide-white/5 border-r border-white/5 bg-black/10">
                @foreach ($users as $user)
                    <li
                        data-parent=true
                        x-data="clickHandler"
                        x-on:click="handleNavigation($event)"
                        wire:key="user-{{ $user->id }}"
                    >
                        <div class="group flex items-center gap-3 px-6 py-4 transition-colors hover:bg-gray-800/20">
                            <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 overflow-hidden bg-gray-800 transition-opacity group-hover:opacity-90">
                                <img
                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->username }}"
                                />
                            </figure>
                            <div class="min-w-0 flex flex-1 flex-col overflow-hidden text-left text-sm">
                                <a
                                    class="flex items-center gap-2"
                                    href="{{ route('profile.show', ['username' => $user->username]) }}"
                                    wire:navigate
                                    x-ref="parentLink"
                                >
                                    <p class="truncate font-medium text-white">
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
                                <p class="truncate text-slate-400 transition-colors group-hover:text-slate-300">
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
