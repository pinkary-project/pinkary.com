@props(['channel'])

<a
    href="{{ route('channels.show', $channel) }}"
    {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-1 rounded-md bg-slate-100/80 px-1.5 py-0.5 text-[0.72rem] font-medium whitespace-nowrap text-slate-600 transition hover:bg-pink-50 hover:text-pink-600 dark:bg-[#111a2d] dark:text-slate-400 dark:hover:bg-pink-500/10 dark:hover:text-pink-400']) }}
    data-navigate-ignore="true"
    wire:navigate
>
    <x-heroicon-o-tag class="size-3 shrink-0 text-pink-500/80" />
    <span>{{ $channel->name }}</span>
</a>
