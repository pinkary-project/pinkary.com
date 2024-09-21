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
            :in-thread="false"
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
            :in-thread="false"
            :key="$parentId"
        />
    @endif
    <x-post-divider />
    <livewire:questions.show
        :questionId="$questionId"
        :in-thread="false"
        :key="$questionId"
    />
</div>
