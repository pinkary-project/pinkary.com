<button
    x-cloak
    x-data="copyUrl"
    x-show="isVisible"
    x-on:click="
        copyToClipboard(
            '{{
                route('questions.show', [
                    'username' => $question->to->username,
                    'question' => $question,
                ])
            }}',
        )
    "
    type="button"
    class="text-slate-500 transition-colors hover:text-slate-400 focus:outline-none"
>
    <x-icons.link class="size-4" />
</button>
