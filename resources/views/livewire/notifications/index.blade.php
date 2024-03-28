<div class="flex flex-col gap-2">
    @foreach ($notifications as $notification)
        @php
            $question = \App\Models\Question::find($notification->data['question_id']);

            if ($question === null) {
                $notification->delete();

                continue;
            }
        @endphp

        <a href="{{ route('notifications.show', ['notification' => $notification->id]) }}" wire:navigate>
            <div class="p-4 overflow-hidden transition-colors border rounded-2xl hover:bg-slate-900 group border-slate-900 bg-slate-950 bg-opacity-80">
                @if ($question->from->is(auth()->user()) && $question->answer !== null)
                    <div class="flex items-center gap-3 text-sm text-slate-500">
                        <figure class="flex-shrink-0 w-10 h-10 transition-opacity rounded-full bg-slate-800 group-hover:opacity-90">
                            <img
                                src="{{ $question->to->avatar ? url($question->to->avatar) : $question->to->avatar_url }}"
                                alt="{{ $question->to->username }}"
                                class="w-10 h-10 rounded-full"
                            />
                        </figure>
                        <p>
                            {{ $question->to->name }} answered your {{ $question->anonymously ? 'anonymous' : '' }} question:
                        </p>
                    </div>
                @else
                    <div class="flex items center">
                        <div>
                                @if ($question->anonymously)
                                    <div class="flex items-center gap-3 text-sm text-slate-500">
                                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 border border-dashed rounded-full border-1 border-slate-400">
                                            <span>?</span>
                                        </div>

                                        <p>Someone asked you anonymously:</p>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 text-sm text-slate-500">
                                        <figure class="flex-shrink-0 w-10 h-10 transition-opacity rounded-full bg-slate-800 group-hover:opacity-90">
                                            <img
                                                src="{{ $question->from->avatar ? url($question->from->avatar) : $question->from->avatar_url }}"
                                                alt="{{ $question->from->username }}"
                                                class="w-10 h-10 rounded-full"
                                            />
                                        </figure>
                                        <p>
                                            {{ $question->from->name }} asked you:
                                        </p>
                                    </div>

                                @endif
                        </div>
                    </div>
                @endif

                <p class="mt-2 text-slate-200">
                    {!! $question->content !!}
                </p>
            </div>
        </a>
    @endforeach

    @if ($user->notifications->count() === 0)
        <div class="rounded-lg">
            <p class="text-slate-400">No pending questions.</p>
        </div>
    @endif
</div>
