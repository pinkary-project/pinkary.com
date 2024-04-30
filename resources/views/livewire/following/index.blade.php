<x-modal
    name="following"
    maxWidth="2xl"
>
    <div class="p-10">
        <div>
            @if ($following->count())
                <strong> <span>@</span>{{ $user->username }} following </strong>
            @else
                <strong> <span>@</span>{{ $user->username }} does not have any following </strong>
            @endif
        </div>

        @if ($following->count())
            <section class="mt-10 max-w-2xl">
                <ul class="flex flex-col gap-2">
                    @foreach ($following as $user)
                        <li>
                            <a
                                href="{{ route('profile.show', ['username' => $user->username]) }}"
                                class="group flex items-center gap-3 rounded-2xl border border-slate-900 bg-slate-950 bg-opacity-80 p-4 transition-colors hover:bg-slate-900"
                                wire:navigate
                            >
                                <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                        src="{{ $user->avatar_url }}"
                                        alt="{{ $user->username }}"
                                    />
                                </figure>
                                <div class="flex flex-col overflow-hidden text-sm">
                                    <div class="flex items-center space-x-2">
                                        <p class="truncate font-medium">
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
                                    </div>
                                    <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                                        {{ '@'.$user->username }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="mt-5">
                {{ $following->links() }}
            </div>
        @endif
    </div>
</x-modal>
