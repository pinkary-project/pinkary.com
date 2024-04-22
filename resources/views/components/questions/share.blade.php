<button
    x-cloak
    x-data="shareProfile"
    x-show="isVisible"
    x-on:click="
        share({
            url: '{{
                route('questions.show', [
                    'username' => $question->to->username,
                    'question' => $question,
                ])
            }}',
        })
    "
    class="text-slate-500 transition-colors hover:text-slate-400 focus:outline-none"
>
    <x-icons.paper-airplane class="h-4 w-4" />
</button>
