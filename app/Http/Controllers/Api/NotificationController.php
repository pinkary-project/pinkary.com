<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Notifications\FormatNotificationRow;
use App\Http\Requests\Api\PaginatedRequest;
use App\Livewire\Concerns\HasNotificationLoaders;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

final readonly class NotificationController
{
    use HasNotificationLoaders;

    /** List the signed-in user's notifications. */
    public function index(PaginatedRequest $request, FormatNotificationRow $format): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = $user->notifications()->simplePaginate($request->perPage());

        /** @var Collection<int, DatabaseNotification> $notifications */
        $notifications = $paginator->getCollection();

        $questions = $this->questionsFor($notifications);
        $followers = $this->followersFor($notifications);

        $items = $notifications
            ->map(fn (DatabaseNotification $notification): ?array => $format->handle(
                $notification,
                $user,
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
            'meta' => ['unread_count' => $user->unreadNotifications()->count()],
        ]);
    }

    /** Delete one of the signed-in user's notifications. */
    public function destroy(Request $request, string $notification): Response
    {
        /** @var User $user */
        $user = $request->user();

        $user->notifications()->where('id', $notification)->firstOrFail()->delete();

        return response()->noContent();
    }
}
