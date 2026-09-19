<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;

final readonly class EnsureCanPublish
{
    public function handle(User $user, int $incoming = 1): void
    {
        if (app()->isLocal()) {
            return;
        }

        if ($user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            throw new HttpResponseException(response()->json(['message' => 'You can only send 3 questions per minute.'], 429));
        }

        if ($user->questionsSent()->where('created_at', '>=', now()->subDay())->count() + $incoming > 30) {
            throw new HttpResponseException(response()->json(['message' => 'You can only send 30 questions per day.'], 429));
        }
    }
}
