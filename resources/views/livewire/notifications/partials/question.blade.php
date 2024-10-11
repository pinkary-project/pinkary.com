<div class="flex items-center gap-3 text-sm text-slate-500">
    <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
        <img
            src="{{ $question->from->avatar_url }}"
            alt="{{ $question->from->username }}"
            class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>
    <p><span class="dark:text-white text-black">{{ $question->from->name }}</span> asked you:</p>
</div>
