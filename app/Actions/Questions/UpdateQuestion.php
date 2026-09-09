<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Channel;
use App\Models\Question;
use Illuminate\Support\Facades\Cache;

final readonly class UpdateQuestion
{
    /**
     * Persist the question update and related channel counts.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{previousChannel: ?Channel, channel: ?Channel}
     */
    public function handle(
        Question $question,
        array $attributes,
        bool $syncChannel,
        ?int $previousChannelId,
        bool $clearLikes,
    ): array {
        $question->update($attributes);

        $previousChannel = null;
        $channel = null;

        if ($syncChannel) {
            /** @var int|null $channelId */
            $channelId = $attributes['channel_id'] ?? null;

            if ($previousChannelId !== $channelId) {
                if ($previousChannelId !== null) {
                    Channel::whereKey($previousChannelId)->where('questions_count', '>', 0)->decrement('questions_count');
                    $previousChannel = Channel::find($previousChannelId);
                }

                if ($channelId !== null) {
                    $channel = Channel::find($channelId);
                    if ($channel instanceof Channel) {
                        $channel->increment('questions_count');
                    }
                }

                Cache::forget('channels:popular');
            }
        }

        if ($clearLikes) {
            $question->likes()->delete();
        }

        return [
            'previousChannel' => $previousChannel instanceof Channel ? $previousChannel : null,
            'channel' => $channel instanceof Channel ? $channel : null,
        ];
    }
}
