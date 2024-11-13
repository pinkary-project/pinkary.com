@props([
    'rootId' => null,
    'grandParentId' => null,
    'parentId' => null,
    'questionId' => null,
    'username' => null,
])

<ul
    wire:key="thread-inner-{{ $questionId.'-'.$rootId.'-'.$parentId }}"
    role="list"
    class="divide-y divide-white/5"
>
    @if ($rootId !== null)
        <li class="cursor-pointer hover:bg-gray-800/20">
            <livewire:questions.show
                :questionId="$rootId"
                :in-thread="true"
                :key="'question-'.$rootId"
            />
        </li>

        @if($grandParentId !== null && ($parentId === null || $grandParentId !== $rootId))
            <x-post-divider
                :link="route('questions.show', ['username' => $username, 'question' => $rootId])"
                text="View more comments"
                wire:key="divider-{{ $parentId }}"
            />
        @else
            <x-post-divider wire:key="divider-{{ $parentId }}"/>
        @endif
    @endif

    @if ($parentId !== null && $rootId !== $parentId)
        <li class="cursor-pointer hover:bg-gray-800/20">
            <livewire:questions.show
                :questionId="$parentId"
                :in-thread="$rootId !== null"
                :key="'question-'.$parentId"
            />
        </li>

        <x-post-divider
            wire:key="divider-{{ $questionId }}"
        />
    @endif

    <li class="cursor-pointer hover:bg-gray-800/20">
        <livewire:questions.show
            :questionId="$questionId"
            :in-thread="$rootId !== null || $parentId !== null"
            :key="'question-'.$questionId"
        />
    </li>
</ul>
