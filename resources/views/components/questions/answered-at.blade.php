<time
    class="cursor-help"
    title="{{ $question->answered_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
    datetime="{{ $question->answered_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
>
    {{ $question->answered_at->timezone(session()->get('timezone', 'UTC'))->diffForHumans() }}
</time>
