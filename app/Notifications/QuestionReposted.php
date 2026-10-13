<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Repost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class QuestionReposted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Repost $repost) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'question_id' => $this->repost->question_id,
            'repost_id' => $this->repost->id,
            'reposter_id' => $this->repost->user_id,
        ];
    }
}
