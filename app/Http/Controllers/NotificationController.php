<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Features\SupportRedirects\Redirector;

final class NotificationController
{
    public function update(User $user, DatabaseNotification $notification, Question $question): RedirectResponse|Redirector
    {
        $notification->markAsRead();

        $question = Question::findOrFail($notification->data['question_id']);

        return redirect()->route('questions.show', [
            'user' => $user,
            'question' => $question,
        ]);
    }
}
