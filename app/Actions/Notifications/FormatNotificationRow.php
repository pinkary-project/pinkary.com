<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use App\Notifications\UserFollowed;
use App\Notifications\UserMentioned;
use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

final readonly class FormatNotificationRow
{
    /**
     * Shape one row, mirroring the web's notification components.
     * Returns null when the subject is gone (the web skips those rows).
     *
     * @param  Collection<string, Question>  $questions
     * @param  Collection<int, User>  $followers
     * @return array<string, mixed>|null
     */
    public function handle(
        DatabaseNotification $notification,
        User $viewer,
        Collection $questions,
        Collection $followers,
        Request $request,
    ): ?array {
        $row = match ($notification->type) {
            UserFollowed::class => $this->followedRow($notification, $followers, $request),
            UserMentioned::class => $this->mentionRow($notification, $questions, $request),
            QuestionCreated::class, QuestionAnswered::class => $this->questionRow($notification, $viewer, $questions, $request),
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
    private function followedRow(DatabaseNotification $notification, Collection $followers, Request $request): ?array
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
    private function mentionRow(DatabaseNotification $notification, Collection $questions, Request $request): ?array
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
    private function questionRow(DatabaseNotification $notification, User $viewer, Collection $questions, Request $request): ?array
    {
        $question = $this->findQuestion($notification, $questions);

        if (! $question) {
            return null;
        }

        $isComment = $question->parent_id !== null;
        $isAnswer = ! $isComment && $question->from?->is($viewer) && $question->answer !== null;
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
        if (! in_array($notification->type, [UserMentioned::class, QuestionCreated::class, QuestionAnswered::class], true)) {
            return null;
        }

        $id = $notification->data['question_id'] ?? null;

        if (! is_string($id)) {
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
