<?php

declare(strict_types=1);

namespace App\Mail;

use App\Actions\Mail\RecordSuppressedEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\MailcoachMailer\Exceptions\EmailNotValid;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Throwable;

final class PendingNotifications extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly User $user,
        public readonly int $pendingNotificationsCount,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🌸 Pinkary: You Have '.$this->pendingNotificationsCount.' '.str('Notification')->plural($this->pendingNotificationsCount).'! - '.now()->format('F j, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.pending-notifications',
            with: [
                'date' => now()->format('Y-m-d'),
                'user' => $this->user,
                'pendingNotificationsCount' => $this->pendingNotificationsCount,
            ],
        );
    }

    /**
     * Handle a permanent delivery failure by suppressing future mails.
     */
    public function failed(Throwable $throwable): void
    {
        $statusCode = $throwable instanceof EmailNotValid
            ? 422
            : ($throwable instanceof HttpTransportException ? $throwable->getResponse()->getStatusCode() : null);

        if (! in_array($statusCode, [406, 422], true)) {
            return;
        }

        app(RecordSuppressedEmail::class)->handle($this->user->email, "mailcoach-{$statusCode}");
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
