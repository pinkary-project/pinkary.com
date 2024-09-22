<div class="relative flex w-full items-center justify-center gap-3 overflow-hidden text-2xl">
    <div class="absolute left-0 h-full w-24 bg-gradient-to-r dark:from-slate-950 from-slate-100 to-transparent"></div>
    <div class="absolute right-0 h-full w-24 bg-gradient-to-l dark:from-slate-950 from-slate-100 to-transparent"></div>

    @foreach ($users as $user)
        <a
            class="flex-shrink-0 transition-opacity hover:opacity-90"
            href="{{ route('profile.show', ['username' => $user->username]) }}"
        >
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->username }}"
                class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
            />
        </a>
    @endforeach
</div>
