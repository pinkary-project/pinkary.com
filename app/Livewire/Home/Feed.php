<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Feed extends Component
{
    use HasLoadMore;

    /**
     * The hashtag name that the queried questions should relate to.
     */
    public ?string $hashtag = null;

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
        $questions = (new RecentQuestionsFeed($this->hashtag))
            ->builder()
            ->simplePaginate($this->perPage);

        return view('livewire.feed', [
            'questions' => $questions,
        ]);
    }
}
