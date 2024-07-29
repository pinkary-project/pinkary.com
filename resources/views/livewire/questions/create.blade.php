<div>
    <textarea
        wire:model.live="content"
        class="w-full p-2 border rounded bg-black"
        rows="4"
        placeholder="What's happening?"
    ></textarea>

    @if ($content && $suggestedTags)
        <div class="relative">
            <ul class="absolute w-full bg-gray-500 text-white border rounded shadow">
                @foreach ($suggestedTags as $tag)
                    <li
                        wire:click="selectTag('{{ $tag->name }}')"
                        class="p-2 cursor-pointer hover:bg-gray-100"
                    >
                        {{ $tag->name }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <button
        wire:click="submit"
        class="mt-2 px-4 py-2 bg-blue-500 text-white rounded"
    >
        Share
    </button>
</div>
