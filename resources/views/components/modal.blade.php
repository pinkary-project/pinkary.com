@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'showCloseButton' => true,
    'closeButtonOutsideModal' => false,
    'shouldCenterModalContent' => false
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];

    $closeButtonPosition = $closeButtonOutsideModal ? 'right-0 -top-10' : 'right-2 top-2';
    $contentOverflowStyle = ($closeButtonOutsideModal && !$shouldCenterModalContent) ? 'mt-10' : '';
    $modalContentPosition = $shouldCenterModalContent ? 'flex justify-center items-center' : '';
@endphp

<div
    x-data="{
        show: @js($show),
        showCloseButton: @js($showCloseButton),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
        open(name) {
            this.show = true;
            this.$dispatch('modal-opened', name);
        },
        close(name) {
            this.show = false;
            this.$dispatch('modal-closed', name);
        }
    }"
    x-init="
        $watch('show', (value) => {
            if (value) {
                document.body.classList.add('overflow-y-hidden')
                {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
            } else {
                document.body.classList.remove('overflow-y-hidden')
            }
        })
    "
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? open('{{ $name }}') : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? close('{{ $name }}') : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto {{$modalContentPosition}} bg-clip-padding px-4 py-6 backdrop-blur-sm backdrop-filter sm:px-0"
    style="display: {{ $show ? 'block' : 'none' }}"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-slate-500 bg-opacity-25"></div>
    </div>
    <div
        x-show="show"
        class="{{ $maxWidth }} {{$contentOverflowStyle}} transform rounded-lg dark:bg-slate-950 bg-slate-50 shadow-xl transition-all sm:mx-auto sm:w-auto"
        x-transition:enter="duration-300 ease-out"
        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="duration-200 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
    >
        <div>
            <button
                x-show="showCloseButton == true"
                x-on:click="show = false"
                class="absolute text-xl focus:outline-none z-50 {{$closeButtonPosition}}"
            >
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
