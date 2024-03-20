<div class="mb-12 w-full px-2 text-slate-200">
    <div class="mb-8 w-full max-w-md">
        <div class="relative flex items-center py-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-5 h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" ></path></svg>
            <input
                wire:model.live.debounce.500ms="query"
                class="w-full rounded-2xl border border-slate-900 bg-gray-950 py-3 pl-14 pr-4 transition-all placeholder:text-slate-500"
                type="text"
                name="q"
                placeholder="Search for users..."
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
                    <li>
                        <a
                            href="{{ route('profile.show', ['user' => $user->username]) }}"
                            class="flex items-center gap-3 rounded-2xl border border-slate-900 bg-gray-950 bg-opacity-80 p-4 transition-colors hover:bg-slate-900"
                            wire:navigate
                        >
                            <figure class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-slate-800 transition-opacity hover:opacity-90">
                                <img
                                    class="h-12 w-12 rounded-full"
                                    src="{{ $user->avatar ? url($user->avatar) : $user->avatar_url }}"
                                    alt="{{ $user->username }}"
                                />
                            </figure>
                            <div class="flex flex-col overflow-hidden text-sm">
                                <div class="flex">
                                    <p class="truncate font-medium">
                                        {{ $user->name }}
                                    </p>
                                    @if ($user->is_verified)
                                        <svg aria-label="Verified" class="text-{{ $user->right_color }} ml-1 mt-0.5 flex-shrink-0 fill-current saturate-200" height="14" role="img" viewBox="0 0 40 40" width="18"><title>Verified</title><path d="M19.998 3.094 14.638 0l-2.972 5.15H5.432v6.354L0 14.64 3.094 20 0 25.359l5.432 3.137v5.905h5.975L14.638 40l5.36-3.094L25.358 40l3.232-5.6h6.162v-6.01L40 25.359 36.905 20 40 14.641l-5.248-3.03v-6.46h-6.419L25.358 0l-5.36 3.094Zm7.415 11.225 2.254 2.287-11.43 11.5-6.835-6.93 2.244-2.258 4.587 4.581 9.18-9.18Z" fill-rule="evenodd" ></path></svg>
                                    @endif
                                </div>
                                <p class="truncate text-slate-500 transition-colors hover:text-slate-400">
                                    {{ '@'.$user->username }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
