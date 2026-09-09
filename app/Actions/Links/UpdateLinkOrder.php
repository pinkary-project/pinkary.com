<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\User;

final readonly class UpdateLinkOrder
{
    /**
     * Persist the user's link order.
     *
     * @param  array<int, string>  $sort
     */
    public function handle(User $user, array $sort): void
    {
        $sort = collect($sort)
            ->map(fn (string $linkId): ?int => $user->links->contains($linkId) ? ((int) $linkId) : null)
            ->filter()
            ->values()
            ->toArray();

        $user->update([
            'links_sort' => count($sort) === 0 ? null : $sort,
        ]);
    }
}
