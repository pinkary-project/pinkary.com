@props([
    'rootId' => null,
    'grandParentId' => null,
    'parentId' => null,
    'questionId' => null,
    'username' => null,
])

<div wire:key="thread-inner-{{ $questionId.'-'.$rootId.'-'.$parentId }}">
    @if ($rootId !== null)
        <livewire:questions.show
            :questionId="$rootId"
            :in-thread="true"
            :key="'question-'.$rootId"
        />

        @if($grandParentId !== null && ($parentId === null || $grandParentId !== $rootId))
            <x-post-divider
                :link="route('questions.show', ['username' => $username, 'question' => $rootId])"
                text="View more comments"
                wire:key="divider-{{ $parentId }}"
            />
        @else
            <x-post-divider wire:key="divider-{{ $parentId }}" />
        @endif
    @endif

    @if ($parentId !== null && $rootId !== $parentId)
        <livewire:questions.show
            :questionId="$parentId"
            :in-thread="$rootId !== null"
            :key="'question-'.$parentId"
        />

        <x-post-divider
            wire:key="divider-{{ $questionId }}"
        />
    @endif

    <livewire:questions.show
        :questionId="$questionId"
        :in-thread="$rootId !== null || $parentId !== null"
        :key="'question-'.$questionId"
    />
</div>
