@props([
    'question' => null,
])

@php
    $question->loadMissing('children');
@endphp

@if($question->children->isNotEmpty())
        <ul class="divide-y divide-white/5">
            @foreach($question->children as $comment)
                <li
                    class="cursor-pointer hover:bg-gray-800/20"
                    style="padding-left: {{ $loop->depth * 16 }}px"
                >
                    @break($loop->depth > 5)

                    <livewire:questions.show :question-id="$comment->id" :inThread='true' :wire:key="$comment->id"/>
                </li>

                <x-comments :question="$comment"/>
            @endforeach
        </ul>
@endif
