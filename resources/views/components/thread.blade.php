@props([
    'rootId' => null,
    'grandParentId' => null,
    'parentId' => null,
    'questionId' => null,
    'username' => null,
    'inIndex' => true,
    'repostId' => null,
    'feedKey' => null,
])

@php($threadKey = $feedKey ?? $questionId)

<div wire:key="thread-inner-{{ $threadKey.'-'.$rootId.'-'.$parentId }}">
    @if ($repostId === null && $rootId !== null)
        <livewire:questions.show
            :questionId="$rootId"
            :in-thread="true"
            :in-index="$inIndex"
            :key="'question-'.$threadKey.'-'.$rootId"
        />

        @if ($grandParentId !== null && ($parentId === null || $grandParentId !== $rootId))
            <x-post-divider
                :link="route('questions.show', ['username' => $username, 'question' => $rootId])"
                text="View more comments"
                wire:key="divider-{{ $parentId }}"
            />
        @else
            <x-post-divider wire:key="divider-{{ $parentId }}" />
        @endif
    @endif

    @if ($repostId === null && $parentId !== null && $rootId !== $parentId)
        <livewire:questions.show
            :questionId="$parentId"
            :in-thread="$rootId !== null"
            :in-index="$inIndex"
            :key="'question-'.$threadKey.'-'.$parentId"
        />

        <x-post-divider wire:key="divider-{{ $questionId }}" />
    @endif

    <livewire:questions.show
        :questionId="$questionId"
        :repostId="$repostId"
        :in-thread="false"
        :in-index="$inIndex"
        :key="'question-'.$threadKey.'-'.$questionId"
    />
</div>
