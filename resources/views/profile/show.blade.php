<x-app-layout>
    <div class="space-y-0 py-0">
        <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95 p-4 sm:p-6">
            <livewire:links.index :userId="$user->id" />
        </section>

        <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95 p-4 sm:p-6">
            <div class="border border-slate-800/30 bg-[#0b1324] p-4">
                <livewire:questions.create :toId="$user->id" />
            </div>

            <div class="mt-4">
                <livewire:questions.index
                    :userId="$user->id"
                />
            </div>
        </section>
    </div>
</x-app-layout>
