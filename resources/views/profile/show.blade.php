<x-app-layout>
    <div class="flex flex-col items-center justify-center py-10">
        <div class="min-h-screen w-full max-w-md overflow-hidden rounded-lg px-4 md:px-0">
            <livewire:links.index :userId="$user->id" />
            @if($user->prefers_questions || auth()->id() === $user->id)
                <livewire:questions.create :toId="$user->id" />
            @else
                <p>{{__("The user doesn't accept questions.")}}</p>
            @endif
            <livewire:questions.index
                :userId="$user->id"
            />
        </div>
    </div>
</x-app-layout>
