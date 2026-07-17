@props([
    'question' => null,
    'depth' => 0,
])

@php
    $question->loadMissing('children.children');
@endphp

@if ($question->children->isNotEmpty())
    <div @class([
        'divide-y divide-slate-200/70 dark:divide-slate-800/30' => $depth === 0,
    ])>
        @foreach ($question->children as $comment)
            @break($loop->depth > 5)
            @php
                $showThreadContinuation = ! $loop->last || $comment->children->isNotEmpty();
            @endphp

            <div @class([
                'py-6 first:pt-0 last:pb-0' => $depth === 0,
            ])>
                <livewire:questions.show
                    :question-id="$comment->id"
                    :inThread="$showThreadContinuation"
                    :key="$comment->id"
                />

                @if ($showThreadContinuation)
                    <x-post-divider wire:key="comment-divider-{{ $comment->id }}" />
                @endif

                <x-comments :question="$comment" :depth="$depth + 1" />
            </div>
        @endforeach
    </div>
@endif
