<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReadNotificationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final readonly class NotificationReadController
{
    /** Mark one notification, or all of them, as read. */
    public function store(ReadNotificationRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();

        if (isset($validated['id'])) {
            $user->notifications()->whereKey($validated['id'])->first()?->markAsRead();
        } else {
            $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return response()->json(['data' => [
            'unread_count' => $user->unreadNotifications()->count(),
        ]]);
    }
}
