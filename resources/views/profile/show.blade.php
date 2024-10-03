<x-app-layout>
    <div class="flex flex-col items-center justify-center py-10">
        <div class="min-h-screen w-full max-w-md overflow-hidden rounded-lg px-4 dark:shadow-md md:px-0">
            <livewire:links.index :userId="$user->id" />
            @if($user->prefers_questions)
                <livewire:questions.create :toId="$user->id" />
            @endif
            <livewire:questions.index
                :userId="$user->id"
                :pinnable="true"
            />
        </div>
    </div>
</x-app-layout>
