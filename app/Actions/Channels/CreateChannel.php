<?php

declare(strict_types=1);

namespace App\Actions\Channels;

use App\Models\Channel;
use App\Models\User;

final readonly class CreateChannel
{
    /**
     * Find a channel by slug or create it for the user.
     */
    public function handle(User $user, string $name, string $slug): Channel
    {
        return Channel::query()->createOrFirst(
            ['slug' => $slug],
            [
                'user_id' => $user->id,
                'name' => $name,
                'questions_count' => 0,
            ],
        );
    }
}
