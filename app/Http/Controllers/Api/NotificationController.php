<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Notifications\FormatNotificationRow;
use App\Http\Requests\Api\PaginatedRequest;
use App\Livewire\Concerns\HasNotificationLoaders;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

final class NotificationController
{
    use HasNotificationLoaders;

    public function index(PaginatedRequest $request, FormatNotificationRow $format): JsonResponse
    {
        $perPage = $request->validated()['per_page'] ?? 20;

        $paginator = $request->user()->notifications()->simplePaginate($perPage);

        /** @var Collection<int, DatabaseNotification> $notifications */
        $notifications = $paginator->getCollection();

        $questions = $this->questionsFor($notifications);
        $followers = $this->followersFor($notifications);

        $items = $notifications
            ->map(fn (DatabaseNotification $notification): ?array => $format->handle(
                $notification,
                $request->user(),
                $questions,
                $followers,
                $request,
            ))
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'data' => $items,
            'links' => ['next' => $paginator->nextPageUrl()],
            'meta' => ['unread_count' => $request->user()->unreadNotifications()->count()],
        ]);
    }
}
