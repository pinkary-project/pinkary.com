<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

final readonly class FeedThread
{
    /**
     * @param  Paginator<int, Question>  $paginator
     * @return Paginator<int, Question>
     */
    public function attachTo(Paginator $paginator, ?int $userId, int $limit = 2): Paginator
    {
        $items = $paginator->getCollection();
        $threads = $this->forItems($items, $userId, $limit);

        foreach ($items as $item) {
            $thread = $threads[$item->id] ?? null;

            $item->setRelation('threadChain', $thread['posts'] ?? collect());
            $item->setAttribute('threadMore', $thread['more'] ?? false);
            $item->setAttribute('threadMoreId', $thread['more_id'] ?? null);
        }

        return $paginator;
    }

    /**
     * @param  Collection<int, Question>  $items
     * @return array<string, array{posts: Collection<int, Question>, more: bool, more_id: ?string}>
     */
    public function forItems(Collection $items, ?int $userId, int $limit = 2): array
    {
        /** @var list<string> $ids */
        $ids = $items
            ->flatMap(fn (Question $item): array => array_filter([$item->parent_id, $item->root_id]))
            ->unique()
            ->values()
            ->all();

        $hydrated = $ids === []
            ? collect()
            : (new FeedQuestion)(Question::query()->whereIn('id', $ids)->tap(new WhereNotModerated), $userId)->get()->keyBy('id');

        $threads = [];

        foreach ($items as $item) {
            /** @var list<Question> $chain */
            $chain = [];

            if ($item->root_id !== null && $item->root_id !== $item->id) {
                $root = $hydrated->get($item->root_id);

                if ($root instanceof Question) {
                    $chain[] = $root;
                }
            }

            if (! in_array($item->parent_id, [null, $item->id, $item->root_id], true)) {
                $parent = $hydrated->get($item->parent_id);

                if ($parent instanceof Question) {
                    $chain[] = $parent;
                }
            }

            $chain = array_slice($chain, -$limit);

            $last = end($chain);
            $parent = $last instanceof Question && $last->id === $item->parent_id ? $last : null;
            $grandParentId = $parent?->parent_id;

            $threads[$item->id] = [
                'posts' => collect($chain)->values(),
                'more' => $grandParentId !== null && $grandParentId !== $item->root_id,
                'more_id' => $item->root_id,
            ];
        }

        return $threads;
    }
}
