@props([
    'rootId' => null,
    'grandParentId' => null,
    'parentId' => null,
    'questionId' => null,
    'username' => null,
])

<div wire:key="thread-{{ $questionId.'-'.$rootId.'-'.$parentId }}">
    @if ($rootId !== null)
        <livewire:questions.show
            :questionId="$rootId"
            :in-thread="true"
            :key="'question-'.$rootId"
        />
    @endif
    @if ($parentId !== null && $rootId !== $parentId)
        @if($rootId !== null)
            @if ($grandParentId === $rootId)
                <x-post-divider wire:key="divider-{{ $parentId }}" />
            @else
                <x-post-divider
                    :link="route('questions.show', ['username' => $username, 'question' => $questionId])"
                    :text="'View more comments...'"
                    wire:key="divider-{{ $parentId }}"
                />
            @endif
        @endif
        <livewire:questions.show
            :questionId="$parentId"
            :in-thread="$rootId !== null"
            :key="'question-'.$parentId"
        />
    @endif
    @if ($parentId !== null || $rootId !== null)
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
