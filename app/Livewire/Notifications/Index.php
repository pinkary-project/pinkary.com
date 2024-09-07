<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Ignore all notifications.
     */
    public function ignoreAll(): void
    {
        $user = type(auth()->user())->as(User::class);

        $questionsToIgnore = $user
            ->notifications()
            ->where('type', QuestionCreated::class)
            ->select('data->question_id');

        $user
            ->questionsReceived()
            ->whereIn('id', $questionsToIgnore)
            ->each(function (Question $question): void {
                $question->update(['is_ignored' => true]);
            });

        $user->notifications()->delete();

        $this->dispatch('question.ignored');
        $this->dispatch('notification.created', message: 'Notifications ignored.');
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = type($request->user())->as(User::class);

        return view('livewire.notifications.index', [
            'user' => $user,
            'notifications' => $user->notifications()->get(),
        ]);
    }
}
