@props([
    'rootId' => null,
    'grandParentId' => null,
    'parentId' => null,
    'questionId' => null,
    'username' => null,
])

<div>
    @if ($rootId !== null)
        <livewire:questions.show
            :questionId="$rootId"
            :in-thread="true"
            :key="$rootId"
        />
    @endif
    @if ($parentId !== null && $rootId !== $parentId)
        @if ($grandParentId === $rootId)
            <x-post-divider />
        @else
            <x-post-divider
                :link="route('questions.show', ['username' => $username, 'question' => $rootId])"
                :text="'View more comments...'"
            />
        @endif
        <livewire:questions.show
            :questionId="$parentId"
            :in-thread="true"
            :key="$parentId"
        />
    @endif

    @if ($parentId !== null || $rootId !== null)
        <x-post-divider />
    @endif

    <livewire:questions.show
        :questionId="$questionId"
        :in-thread="true"
        :key="$questionId"
    />
</div>
