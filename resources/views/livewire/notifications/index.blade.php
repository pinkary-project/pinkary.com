<div>
    @foreach ($notifications as $notification)
        @php
            $question = \App\Models\Question::find($notification->data['question_id']);

            if ($question === null) {
                $notification->delete();

                continue;
            }
        @endphp

        <div class="hover:bg-gray-800">
            <a
                href="{{ route('notifications.show', ['notification' => $notification->id]) }}"
                wire:navigate
            >
                <div class="rounded-lg px-2 py-4">

                    @if ($question->from->is(auth()->user()) && $question->answer !== null)
                        <div class="items center flex">
                            <div>
                                <p class="pt-3 text-gray-400">
                                    {{ $question->to->name }} answered your {{ $question->anonymously ? 'anonymous' : '' }} question:
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="items center flex">
                            <div>
                                <p class="pt-3 text-gray-400">
                                    @if ($question->anonymously)
                                        Someone asked you anonymously:
                                    @else
                                        {{ $question->from->name }} asked you:
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    <p class="mt-2 text-gray-200">
                        {!! $question->content !!}
                    </p>
                </div>
            </a>
        </div>

        @if (! $loop->last)
            <div class="border-t border-gray-800"></div>
        @endif
    @endforeach

    @if ($user->notifications->count() === 0)
        <div class="rounded-lg">
            <p class="text-gray-400">No pending questions.</p>
        </div>
    @endif
</div>
