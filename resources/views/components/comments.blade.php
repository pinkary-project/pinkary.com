@props([
    'question' => null,
])

@php
    $question->loadMissing('children');
@endphp

@if($question->children->isNotEmpty())
    <div>
        @foreach($question->children as $comment)
            @break($loop->depth > 5)

            @if (!$loop->last)
                <livewire:questions.show :question-id="$comment->id" :inThread="true" :wire:key="$comment->id" />
            @else
                <livewire:questions.show :question-id="$comment->id" :wire:key="$comment->id" />
            @endif

            <x-comments :question="$comment" />
        @endforeach
    </div>
@endif
