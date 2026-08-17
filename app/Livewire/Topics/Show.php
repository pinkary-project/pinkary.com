<?php

declare(strict_types=1);

namespace App\Livewire\Topics;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Queries\Feeds\TopicQuestionsFeed;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Show extends Component
{
    use HasLoadMore;

    /**
     * The topic ID.
     */
    #[Locked]
    public int $topicId;

    /**
     * The feed sort order.
     */
    #[Url]
    public string $sort = 'recent';

    /**
     * Mount the component.
     */
    public function mount(Topic $topic): void
    {
        $this->topicId = $topic->id;
    }

    /**
     * Set the feed sort order.
     */
    public function setSort(string $sort): void
    {
        $this->sort = in_array($sort, ['recent', 'trending'], true) ? $sort : 'recent';
        $this->perPage = 5;
    }

    /**
     * Toggle follow for this topic.
     */
    public function toggleFollow(#[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $topic = Topic::findOrFail($this->topicId);

        if ($topic->is_system || ! $topic->is_active) {
            return;
        }

        if ($topic->followers()->where('users.id', $user->id)->exists()) {
            $topic->followers()->detach($user->id);
            $this->dispatch('notification.created', message: "Unfollowed {$topic->name}.");
        } else {
            $topic->followers()->attach($user->id);
            $this->dispatch('notification.created', message: "Following {$topic->name}.");
        }
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
    #[On('question.updated')]
    public function refresh(): void {}

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] ?User $user): View
    {
        $topic = Topic::withCount(['followers', 'questions'])->findOrFail($this->topicId);

        $questions = (new TopicQuestionsFeed($topic, $this->sort))
            ->builder()
            ->simplePaginate($this->perPage);

        $isFollowed = $user instanceof User && $topic->isFollowedBy($user);

        return view('livewire.topics.show', [
            'topic' => $topic,
            'questions' => $questions,
            'isFollowed' => $isFollowed,
        ]);
    }
}
