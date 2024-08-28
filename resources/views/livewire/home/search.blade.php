<div class="mb-12 w-full text-slate-200">
    <div class="mb-8 w-full max-w-md">
        <div class="relative flex items-center py-1">

            <x-icons.magnifying-glass class="absolute left-5 z-50 size-5"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users and content..."
                class="w-full !rounded-2xl !bg-slate-950 !bg-opacity-80 py-3 pl-14"
            />
        </div>
    </div>

    @if ($results->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">No matching users or content found.</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-3">
                @foreach ($results as $result)
                    <li>
                        @if ($result instanceof App\Models\Question)
                            <livewire:questions.show
                                :questionId="$result->id"
                                :key="'question-' . $result->id"
                            />
                        @elseif ($result instanceof App\Models\User)
                            <x-found-avatar-with-name :user="$result"/>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
