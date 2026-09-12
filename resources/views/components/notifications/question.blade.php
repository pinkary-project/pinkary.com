@props([
    'notification',
    'question',
    'user',
])

@if ($question->parent_id !== null)
    <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
        <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
            <img
                src="{{ $question->from->avatar_url }}"
                alt="{{ $question->from->username }}"
                class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
            />
        </figure>
        <p>
            <span class="font-medium text-slate-950 dark:text-white">{{ $question->from->name }}</span>
            commented on your {{ $question->parent->parent_id !== null ? 'comment' : ($question->parent->isSharedUpdate() ? 'Update' : 'Answer') }}:
        </p>
    </div>
@elseif ($question->from->is($user) && $question->answer !== null)
    <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
        <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
            <img
                src="{{ $question->to->avatar_url }}"
                alt="{{ $question->to->username }}"
                class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
            />
        </figure>
        <p>
            <span class="font-medium text-slate-950 dark:text-white">{{ $question->to->name }}</span>
            answered your {{ $question->anonymously ? 'anonymous question' : 'question' }}:
        </p>
    </div>
@else
    @if ($question->anonymously)
        <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-dashed border-slate-400">
                <span>?</span>
            </div>
            <p>Someone asked you anonymously:</p>
        </div>
    @else
        <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
            <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
                <img
                    src="{{ $question->from->avatar_url }}"
                    alt="{{ $question->from->username }}"
                    class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                />
            </figure>
            <p>
                <span class="font-medium text-slate-950 dark:text-white">{{ $question->from->name }}</span>
                asked you:
            </p>
        </div>
    @endif
@endif

@if (! $question->isSharedUpdate())
    <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{!! $question->content !!}</p>
@endif
