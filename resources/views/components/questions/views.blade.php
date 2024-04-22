@if ($question->views > 0)
    <span class="mx-1">•</span>
    <x-icons.chart class="h-4 w-4" />
    <p class="ml-1 cursor-help" title="{{ Number::format($question->views) }} {{ str('view')->plural($question->views) }}">
        {{ Number::abbreviate($question->views) }} {{ str('view')->plural($question->views) }}
    </p>
@endif
