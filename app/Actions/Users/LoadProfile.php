<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\Scopes\WhereNotModerated;
use App\Models\User;

final readonly class LoadProfile
{
    public function handle(User $user): User
    {
        $user->loadMissing(['links' => fn ($query) => $query->where('is_visible', true)]);
        $user->loadCount(['followers', 'following']);
        $user->setAttribute('posts_count', $user->questionsReceived()->tap(new WhereNotModerated)->where('answer', '!=', '')->count());

        return $user;
    }
}
