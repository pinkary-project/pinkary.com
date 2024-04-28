<x-app-layout>
    <div class="flex flex-col items-center py-10">
        <div class="flex w-full max-w-md flex-col gap-12 overflow-hidden">
            <a
                href="{{ url()->previous() }}"
                class="flex text-slate-400 hover:underline"
                wire:navigate
            >
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>
            <livewire:questions.show :questionId="$question->id" />
            <livewire:comments.index :questionId="$question->id" />
            <livewire:comments.create :questionId="$question->id" />
        </div>
    </div>
</x-app-layout>
