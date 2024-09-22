@props([
    'question' => null,
])

@php
    $question->loadMissing('children');
@endphp

@if($question->children->isNotEmpty())
    <div class="pl-3">
        @foreach($question->children as $comment)
            @break($loop->depth > 5)

            <livewire:questions.show :question-id="$comment->id" :inThread='true' :wire:key="$comment->id" />

            <x-comments :question="$comment" />
        @endforeach
    </div>
@endif
