@props([
    'notification',
    'url' => route('notifications.show', ['notification' => $notification->id]),
])

<div
    data-parent="true"
    x-data="clickHandler"
    x-on:click="handleNavigation($event)"
    class="block cursor-pointer border-b border-slate-200/70 px-4 py-3.5 transition-colors hover:bg-slate-50/80 sm:px-6 sm:py-4 dark:border-slate-800/40 dark:hover:bg-[#0a1325]"
>
    <a href="{{ $url }}" wire:navigate x-ref="parentLink" class="hidden"></a>
    {{ $slot }}
</div>
