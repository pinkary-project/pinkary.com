<div>
    @foreach ($user->notifications as $notification)
        @php
            $question = \App\Models\Question::find($notification->data['question_id']);
        @endphp

        <div class="hover:bg-gray-800">
            <a
                href="{{ route('questions.show', ['user' => $question->to->username, 'question' => $question->id]) }}"
                wire:navigate
            >
                <div class="rounded-lg px-2 py-4">
                    @if ($question->anonymously)
                        <div class="items center flex">
                            <div>
                                <p class="pt-3 text-gray-400">Anonymously</p>
                            </div>
                        </div>
                    @else
                        <div class="items center flex">
                            <img
                                src="{{ $question->from->avatar ? url($question->from->avatar) : $question->from->avatar_url }}"
                                alt="{{ $question->from->username }}"
                                class="h-12 w-12 rounded-full"
                            />
                            <div class="ml-2">
                                <div class="items flex">
                                    <p class="text-gray-400">
                                        {{ $question->from->name }}
                                    </p>
                                    @if ($question->from->is_verified)
                                        <x-icons.verified
                                            :color="$question->from->right_color"
                                            class="ml-1 mt-0.5 h-4 w-4"
                                        />
                                    @endif
                                </div>

                                <div class="text-gray-600 hover:underline">
                                    {{ '@'.$question->from->username }}
                                </div>
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
