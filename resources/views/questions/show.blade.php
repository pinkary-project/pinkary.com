<x-app-layout>
    <div class="flex flex-col items-center py-10">
        <div class="flex w-full max-w-md flex-col gap-12 overflow-hidden">
            <a
                href="{{ route('profile.show', ['user' => $question->to->username]) }}"
                class="flex text-gray-400 hover:underline"
                wire:navigate
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span>Back</span>
            </a>

            <livewire:questions.show :questionId="$question->id" />
        </div>
    </div>
</x-app-layout>
