<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class GetUserQuestions
{
    /**
     * Paginate the user's received questions with feed relations and thread context.
     * Unanswered questions are hidden unless the viewer is the profile owner.
     * Pinned questions appear first.
     */
    public function handle(User $user, ?User $viewer, int $perPage = 20): Paginator
    {
        $viewerId = $viewer?->id;

        $query = Question::query()
            ->where('to_id', $user->id)
            ->whereNull('parent_id')
            ->tap(new WhereNotModerated)
            ->when($user->id !== $viewerId, function (Builder $builder): void {
                $builder->whereNotNull('answer');
            })
            ->orderByDesc('pinned')
            ->orderByDesc('answer_created_at')
            ->orderByDesc('created_at');

        $paginator = (new FeedQuestion)($query, $viewerId)->simplePaginate($perPage);

        /** @var Collection<int, Question> $items */
        $items = $paginator->getCollection();
        $threads = (new FeedThread)->forItems($items, $viewerId);

        foreach ($items as $item) {
            $thread = $threads[$item->id] ?? null;

            $item->setRelation('threadChain', $thread['posts'] ?? collect());
            $item->setAttribute('threadMore', $thread['more'] ?? false);
            $item->setAttribute('threadMoreId', $thread['more_id'] ?? null);
        }

        return $paginator;
    }
}
