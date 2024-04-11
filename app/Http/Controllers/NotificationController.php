<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
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
        $question = type(Question::findOrFail($notification->data['question_id']))->as(Question::class);

        if ($question->answer !== null) {
            $notification->delete();
        }

        return to_route('questions.show', [
            'username' => $question->to->username,
            'question' => $question,
        ]);
    }
}
