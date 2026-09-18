<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Actions\Questions\UpdateQuestionStatus;
use App\Livewire\Concerns\HasNotificationLoaders;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use HasNotificationLoaders, WithPagination;

    /**
     * Ignore all notifications.
     */
    public function ignoreAll(
        string $untilDatetime,
        #[CurrentUser] User $user,
        UpdateQuestionStatus $updateQuestionStatus,
    ): void {
        $questionsToIgnore = $user
            ->notifications()
            ->where('created_at', '<=', $untilDatetime)
            ->where('type', QuestionCreated::class)
            ->select('data->question_id');

        $user
            ->questionsReceived()
            ->whereIn('id', $questionsToIgnore)
            ->each(function (Question $question) use ($updateQuestionStatus): void {
                $updateQuestionStatus->handle($question, ignored: true);
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

        $items = collect($notifications->items());

        return view('livewire.notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
            'questions' => $this->questionsFor($items),
            'followers' => $this->followersFor($items),
        ]);
    }
}
