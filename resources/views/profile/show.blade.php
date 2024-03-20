<x-app-layout>
    <div class="flex flex-col items-center justify-center py-10">
        <div class="w-full max-w-md overflow-hidden rounded-lg shadow-md">
            <livewire:links.index :userId="$user->id" />

            <div class="px-2 md:px-0 lg:px-0 xl:px-0">
                <div class="mt-3 border-t border-gray-800"></div>

                <livewire:questions.create :toId="$user->id" />
                <livewire:questions.index :userId="$user->id" />
            </div>
        </div>
    </div>
</x-app-layout>
