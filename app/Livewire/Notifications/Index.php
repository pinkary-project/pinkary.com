<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Ignore all notifications.
     */
    public function ignoreAll(string $untilDatetime, #[CurrentUser] User $user): void
    {
        $questionsToIgnore = $user
            ->notifications()
            ->where('created_at', '<=', $untilDatetime)
            ->where('type', QuestionCreated::class)
            ->select('data->question_id');

        $user
            ->questionsReceived()
            ->whereIn('id', $questionsToIgnore)
            ->each(function (Question $question): void {
                $question->update(['is_ignored' => true]);
            });

        $user->notifications()
            ->where('created_at', '<=', $untilDatetime)
            ->delete();

        $this->dispatch('question.ignored');
        $this->dispatch('notification.created', message: 'Notifications ignored.');
    }

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        /** @var Paginator<int, DatabaseNotification> $notifications */
        $notifications = $user->notifications()->simplePaginate(10);

        return view('livewire.notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
            'questions' => $this->questionsFor($notifications),
        ]);
    }

    /**
     * Load the questions referenced by the given notifications in a single query.
     *
     * @param  Paginator<int, DatabaseNotification>  $notifications
     * @return Collection<int|string, Question>
     */
    private function questionsFor(Paginator $notifications): Collection
    {
        $questionIds = collect($notifications->items())
            ->map(function (DatabaseNotification $notification): ?string {
                $questionId = $notification->data['question_id'] ?? null;

                return is_string($questionId) ? $questionId : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($questionIds->isEmpty()) {
            return new Collection();
        }

        return Question::query()
            ->with(['from', 'to', 'parent'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');
    }
}
