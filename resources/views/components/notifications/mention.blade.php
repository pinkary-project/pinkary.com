@props([
    'notification',
    'question',
])

@php
    $author = ($question->isSharedUpdate() || $question->answer === null)
        ? $question->from
        : $question->to;
@endphp

<div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
    <figure class="{{ $author->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
        <img
            src="{{ $author->avatar_url }}"
            alt="{{ $author->username }}"
            class="{{ $author->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>
    <p>
        @if ($question->parent !== null)
            You have been mentioned in a comment by
            <span class="font-medium text-slate-950 dark:text-white">{{ '@' . $author->username }}</span>
        @else
            You have been mentioned in a {{ $question->isSharedUpdate() ? 'update by ' : 'question by ' }}<span
             class="font-medium text-slate-950 dark:text-white">{{ '@' . $author->username }}</span>
        @endif
    </p>
</div>

@if (! $question->isSharedUpdate())
    <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{!! $question->content !!}</p>
@endif
