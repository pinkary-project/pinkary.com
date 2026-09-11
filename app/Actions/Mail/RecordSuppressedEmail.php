<?php

declare(strict_types=1);

namespace App\Actions\Mail;

use App\Models\SuppressedEmail;

final readonly class RecordSuppressedEmail
{
    /**
     * Record the email address as suppressed so future mails are skipped.
     */
    public function handle(string $email, ?string $reason = null): SuppressedEmail
    {
        return SuppressedEmail::query()->firstOrCreate(
            ['email' => $email],
            ['reason' => $reason],
        );
    }
}
