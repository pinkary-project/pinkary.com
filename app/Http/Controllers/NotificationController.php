<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use App\Notifications\UserFollowed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

final readonly class NotificationController
{
    /**
     * Display all notifications.
     */
    public function index(): View
    {
        return view('notifications.index');
    }

    /**
     * Display the given notification.
     */
    public function show(DatabaseNotification $notification): RedirectResponse
    {
        return match ($notification->type) {
            UserFollowed::class => $this->handleUserFollowed($notification),
            default => $this->handleQuestionBasedNotification($notification)
        };
    }

    /**
     * Handle the question based notification.
     */
    private function handleQuestionBasedNotification(DatabaseNotification $notification): RedirectResponse
    {
        $question = type(Question::findOrFail($notification->data['question_id']))->as(Question::class);

        if ($question->answer !== null) {
            $notification->delete();
        }

        return to_route('questions.show', [
            'username' => $question->to->username,
            'question' => $question,
        ]);
    }

    /**
     * Handle the UserFollowed notification.
     */
    private function handleUserFollowed(DatabaseNotification $notification): RedirectResponse
    {
        $follower = User::find($notification->data['follower_id']);

        $notification->delete();

        return $follower
            ? to_route('profile.show', ['username' => type($follower)->as(User::class)->username])
            : back();
    }
}
