<?php

declare(strict_types=1);

namespace App\Livewire\Channels;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Channel;
use App\Models\Question;
use App\Queries\Feeds\ChannelQuestionsFeed;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Show extends Component
{
    use HasLoadMore;

    /**
     * The channel instance.
     */
    #[Locked]
    public Channel $channel;

    /**
     * Mount the component.
     */
    public function mount(Channel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * Ignore the given question.
     */
    #[On('question.ignore')]
    public function ignore(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->dispatch('question.ignored');
    }

    /**
     * Refresh the feed.
     */
    #[On('question.created')]
    public function refresh(): void {}

    /**
     * Render the component.
     */
    public function render(): View
    {
        /** @var Paginator<int, Question> $questions */
        $questions = new ChannelQuestionsFeed($this->channel)
            ->builder()
            ->with([
                'from',
                'to',
                'pollOptions',
                'hashtags',
                'channel',
                'parent.from',
                'parent.to',
            ])
            ->simplePaginate($this->perPage);

        return view('livewire.channels.show', [
            'questions' => $questions,
        ]);
    }
}
