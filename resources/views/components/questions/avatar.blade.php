<figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
    <img
        src="{{ $user->avatar_url }}"
        alt="{{ $user->username }}"
        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
    />
</figure>
