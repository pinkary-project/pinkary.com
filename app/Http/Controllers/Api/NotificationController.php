<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Notifications\FormatNotificationRow;
use App\Actions\Questions\UpdateQuestionStatus;
use App\Http\Requests\Api\PaginatedRequest;
use App\Livewire\Concerns\HasNotificationLoaders;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $reposts = $this->repostsFor($notifications);

        $items = $notifications
            ->map(fn (DatabaseNotification $notification): ?array => $format->handle(
                $notification,
                $user,
                $questions,
                $followers,
                $reposts,
                $request,
            ))
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'data' => $items,
            'links' => ['next' => $paginator->nextPageUrl()],
            'meta' => [
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
            ],
        ]);
    }

    /** Ignore received questions and delete notifications up to the given time. */
    public function ignore(Request $request, UpdateQuestionStatus $updateQuestionStatus): Response
    {
        /** @var array{until_datetime: string} $validated */
        $validated = $request->validate([
            'until_datetime' => ['required', 'date'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $untilDatetime = CarbonImmutable::parse($validated['until_datetime']);

        DB::transaction(function () use ($user, $untilDatetime, $updateQuestionStatus): void {
            $questionsToIgnore = $user
                ->notifications()
                ->where('created_at', '<=', $untilDatetime)
                ->where('type', QuestionCreated::class)
                ->select('data->question_id');

            $user
                ->questionsReceived()
                ->whereIn('id', $questionsToIgnore)
                ->each(function (Question $question) use ($updateQuestionStatus): void {
                    $updateQuestionStatus->handle($question, ignored: true);
                });

            $user->notifications()
                ->where('created_at', '<=', $untilDatetime)
                ->delete();
        });

        return response()->noContent();
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
