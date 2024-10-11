<div class="flex items-center gap-3 text-sm text-slate-500">
    <figure
        class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
        <img
            src="{{ $question->to->avatar_url }}"
            alt="{{ $question->to->username }}"
            class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>
    <p>{{ $question->to->name }} answered your {{ $question->anonymously ? 'anonymous' : '' }} question:</p>
</div>
