<button
    @if ($question->likesByUser)
        wire:click="unlike('{{ $question->id }}')"
    @else
        wire:click="like('{{ $question->id }}')"
    @endif
    class="flex items-center transition-colors hover:text-slate-400 focus:outline-none"
>
    @if ($question->likesByUser)
        <x-icons.heart-solid class="h-4 w-4" />
    @else
        <x-icons.heart class="h-4 w-4" />
    @endif

    @if ($question->likes_count > 0)
        <p class="cursor-click ml-1" title="{{ Number::format($question->likes_count) }} {{ str('like')->plural($question->likes_count) }}">{{ Number::abbreviate($question->likes_count) }} {{ str('like')->plural($question->likes_count) }}</p>
    @endif
</button>
