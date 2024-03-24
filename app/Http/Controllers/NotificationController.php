<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

final class NotificationController
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
        $user = auth()->user();
        assert($user instanceof User);

        $question = Question::findOrFail($notification->data['question_id']);

        if ($question->answer !== null) {
            $notification->delete();
        }

        return redirect()->route('questions.show', [
            'user' => $user->username,
            'question' => $question,
        ]);
    }
}
