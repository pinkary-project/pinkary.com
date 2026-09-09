<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class CreateQuestion
{
    /**
     * Persist the question, optional thread posts, and poll options.
     *
     * @param  array<int, array<string, mixed>>  $payloads
     * @param  list<string>  $pollOptions
     * @param  array<int, array<int, string>>  $threadPollOptions
     * @return array<int, Question>
     */
    public function handle(
        User $user,
        array $payloads,
        array $pollOptions,
        array $threadPollOptions,
        ?int $channelId,
    ): array {
        /** @var array<int, Question> $questions */
        $questions = DB::transaction(function () use ($user, $payloads): array {
            /** @var array<int, Question> $created */
            $created = [];

            foreach ($payloads as $index => $payload) {
                if ($index > 0) {
                    $payload['parent_id'] = $created[$index - 1]->id;
                    $payload['root_id'] = $created[0]->id;
                }

                $created[$index] = $user->questionsSent()->create($payload);
            }

            return $created;
        });

        if ($channelId !== null) {
            $channel = Channel::find($channelId);
            if ($channel instanceof Channel) {
                $channel->increment('questions_count');
                Cache::forget('channels:popular');
            }
        }

        if ($pollOptions !== []) {
            $questions[0]->pollOptions()->createMany(
                array_map(
                    fn (string $option): array => ['text' => mb_trim($option), 'votes_count' => 0],
                    $pollOptions,
                ),
            );
        }

        foreach ($questions as $index => $createdQuestion) {
            if ($index === 0) {
                continue;
            }

            if (empty($threadPollOptions[$index - 1])) {
                continue;
            }

            $createdQuestion->pollOptions()->createMany(array_map(
                fn (string $option): array => ['text' => mb_trim($option), 'votes_count' => 0],
                $threadPollOptions[$index - 1],
            ));
        }

        return $questions;
    }
}
