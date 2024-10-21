@php use Illuminate\Support\Str; @endphp

<div class="flex items center gap-3 text-sm text-slate-500">
    <figure
        class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
        <img
            src="{{ $question->from->avatar_url }}"
            alt="{{ $question->from->username }}"
            class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>
    <div class="flex flex-col space-y-1">
        <p>{{ $question->from->name }} Reposted your question:</p>
        <p>
            <span class="text-slate-500 dark:text-slate-200 text-sm">
                {{ Str::words($question->answer, 10) }}
            </span>
        </p>
    </div>
</div>

