<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReadNotificationRequest;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use App\Notifications\UserFollowed;
use App\Notifications\UserMentioned;
use App\Support\AbsoluteUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

final readonly class NotificationController
{
    /**
     * List the authenticated user's notifications, newest first, shaped
     * like the web's notification rows (actor, action line, snippet,
     * navigation target) with the current unread count.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 50);

        $paginator = $request->user()->notifications()->simplePaginate($perPage);

        /** @var Collection<int, DatabaseNotification> $notifications */
        $notifications = $paginator->getCollection();

        $questions = $this->questionsFor($notifications);
        $followers = $this->followersFor($notifications);

        $items = $notifications
            ->map(fn (DatabaseNotification $notification): ?array => $this->shape(
                $request,
                $notification,
                $request->user(),
                $questions,
                $followers,
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

    /**
     * Mark one notification (or all) as read.
     */
    public function read(ReadNotificationRequest $request): JsonResponse
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

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return Collection<string, Question>
     */
    private function questionsFor(Collection $notifications): Collection
    {
        /** @var list<string> $ids */
        $ids = $notifications
            ->map(fn (DatabaseNotification $notification): ?string => $this->questionId($notification))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        return Question::query()
            ->whereIn('id', $ids)
            ->with(['from:id,name,username,avatar,is_verified,is_company_verified', 'to:id,name,username,avatar,is_verified,is_company_verified', 'parent:id,parent_id,content,from_id,to_id'])
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return Collection<int, User>
     */
    private function followersFor(Collection $notifications): Collection
    {
        /** @var list<int> $ids */
        $ids = $notifications
            ->filter(fn (DatabaseNotification $notification): bool => $notification->type === UserFollowed::class)
            ->map(fn (DatabaseNotification $notification): mixed => $notification->data['follower_id'] ?? null)
            ->filter(fn (mixed $id): bool => is_int($id))
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->select('id', 'name', 'username', 'avatar', 'is_verified', 'is_company_verified')
            ->get()
            ->keyBy('id');
    }

    private function questionId(DatabaseNotification $notification): ?string
    {
        if (! in_array($notification->type, [UserMentioned::class, QuestionCreated::class, QuestionAnswered::class], true)) {
            return null;
        }

        $id = $notification->data['question_id'] ?? null;

        return is_string($id) ? $id : null;
    }

    /**
     * Shape one row, mirroring the web's notification components.
     * Returns null when the subject is gone (the web skips those rows).
     *
     * @param  Collection<string, Question>  $questions
     * @param  Collection<int, User>  $followers
     * @return array<string, mixed>|null
     */
    private function shape(
        Request $request,
        DatabaseNotification $notification,
        User $user,
        Collection $questions,
        Collection $followers,
    ): ?array {
        $row = match ($notification->type) {
            UserFollowed::class => $this->followedRow($request, $notification, $followers),
            UserMentioned::class => $this->mentionRow($request, $notification, $questions),
            QuestionCreated::class, QuestionAnswered::class => $this->questionRow($request, $notification, $user, $questions),
            default => null,
        };

        if ($row === null) {
            return null;
        }

        return [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->toIso8601String(),
            ...$row,
        ];
    }

    /**
     * @param  Collection<int, User>  $followers
     * @return array<string, mixed>|null
     */
    private function followedRow(Request $request, DatabaseNotification $notification, Collection $followers): ?array
    {
        $followerId = $notification->data['follower_id'] ?? null;

        if (! is_int($followerId)) {
            return null;
        }

        $follower = $followers->get($followerId);

        if (! $follower) {
            return null;
        }

        return [
            'actor' => $this->actor($follower, $request),
            'action' => 'followed you',
            'snippet' => null,
            'target' => ['kind' => 'user', 'username' => $follower->username],
        ];
    }

    /**
     * @param  Collection<string, Question>  $questions
     * @return array<string, mixed>|null
     */
    private function mentionRow(Request $request, DatabaseNotification $notification, Collection $questions): ?array
    {
        $question = $this->findQuestion($notification, $questions);

        if (! $question) {
            return null;
        }

        $author = ($question->parent_id !== null || $question->content === '__UPDATE__' || $question->answer === null)
            ? $question->from
            : $question->to;

        return [
            'actor' => $author ? $this->actor($author, $request) : null,
            'action' => $question->parent_id !== null
                ? 'mentioned you in a comment:'
                : 'mentioned you in '.($question->isSharedUpdate() ? 'an update:' : 'a question:'),
            'snippet' => $this->snippet($question),
            'target' => ['kind' => 'question', 'id' => $question->id],
        ];
    }

    /**
     * @param  Collection<string, Question>  $questions
     * @return array<string, mixed>|null
     */
    private function questionRow(Request $request, DatabaseNotification $notification, User $user, Collection $questions): ?array
    {
        $question = $this->findQuestion($notification, $questions);

        if (! $question) {
            return null;
        }

        $isComment = $question->parent_id !== null;
        $isAnswer = ! $isComment && $question->from?->is($user) && $question->answer !== null;
        $isAnonymous = ! $isComment && ! $isAnswer && $question->anonymously;

        if ($isComment) {
            $actor = $question->from;
            $action = 'commented on your '.($question->parent?->parent_id !== null
                ? 'comment:'
                : ($question->parent?->isSharedUpdate() ? 'Update:' : 'Answer:'));
        } elseif ($isAnswer) {
            $actor = $question->to;
            $action = 'answered your '.($question->anonymously ? 'anonymous question:' : 'question:');
        } elseif ($isAnonymous) {
            $actor = null;
            $action = 'asked you anonymously:';
        } else {
            $actor = $question->from;
            $action = 'asked you:';
        }

        return [
            'actor' => $actor ? $this->actor($actor, $request) : null,
            'action' => $action,
            'snippet' => $this->snippet($question),
            'target' => ['kind' => 'question', 'id' => $question->id],
        ];
    }

    /**
     * @param  Collection<string, Question>  $questions
     */
    private function findQuestion(DatabaseNotification $notification, Collection $questions): ?Question
    {
        $id = $this->questionId($notification);

        if ($id === null) {
            return null;
        }

        $question = $questions->get($id);

        return $question instanceof Question ? $question : null;
    }

    /** @return array<string, mixed> */
    private function actor(User $user, Request $request): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => AbsoluteUrl::for($user->avatar_url, $request),
            'verified' => (bool) $user->is_verified,
            'company_verified' => (bool) $user->is_company_verified,
        ];
    }

    private function snippet(Question $question): ?string
    {
        $html = $question->content === '__UPDATE__' ? $question->answer : $question->content;

        $text = mb_trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8'));

        return $text === '' ? null : $text;
    }
}
