<x-app-layout>
    <div class="flex flex-col items-center py-10">
        <div class="flex w-full max-w-md flex-col gap-12 overflow-hidden">
            <a href="{{ route('profile.show', ['user' => $question->to->username]) }}" class="flex text-gray-400 hover:underline" wire:navigate>
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>

            <livewire:questions.show :questionId="$question->id" />
        </div>
    </div>
</x-app-layout>
