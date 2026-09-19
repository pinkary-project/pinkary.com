<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Requests\Api\ReadNotificationRequest;
use Illuminate\Http\JsonResponse;

final readonly class ReadController
{
    public function __invoke(ReadNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['id'])) {
            $request->user()->notifications()->whereKey($validated['id'])->first()?->markAsRead();
        } else {
            $request->user()->unreadNotifications->markAsRead();
        }

        return response()->json(['data' => [
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]]);
    }
}
