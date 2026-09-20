<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Actions\Channels\CreateChannel;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class CreateThread
{
    public function __construct(
        private CreateQuestion $createQuestion,
        private CreateChannel $createChannel,
        private EnsureCanPublish $ensureCanPublish,
    ) {}

    /**
     * Publish a shared update (thread) for the given user.
     *
     * The main post plus up to 9 chained follow-ups are stored as
     * self-addressed questions, mirroring the web composer's threads.
     *
     * @param  array<string, mixed>  $validated
     * @return Collection<int, Question>
     */
    public function handle(User $user, array $validated): Collection
    {
        /** @var list<string> $threadPosts */
        $threadPosts = [];
        /** @var array<int, list<string>> $threadPollOptions */
        $threadPollOptions = [];
        /** @var array<int, int> $threadPollDurations */
        $threadPollDurations = [];

        foreach ((array) ($validated['thread_posts'] ?? []) as $index => $post) {
            if (! is_string($post) || mb_trim($post) === '') {
                continue;
            }

            $threadPolls = (array) ($validated['thread_polls'] ?? []);
            $threadPoll = isset($threadPolls[$index]) ? (array) $threadPolls[$index] : null;

            if ($threadPoll !== null) {
                $options = $this->cleanPollOptions($threadPoll['options'] ?? null);
                $duration = (int) ($threadPoll['duration'] ?? 1);

                if ($options === false || $duration < 1 || $duration > 7) {
                    abort(422, 'Each thread poll needs 2 to 4 non-empty options and a duration of 1 to 7 days.');
                }

                $threadPollOptions[] = $options;
                $threadPollDurations[] = $duration;
            } else {
                $threadPollOptions[] = [];
                $threadPollDurations[] = 1;
            }

            $threadPosts[] = $post;
        }

        $pollOptions = $this->cleanPollOptions($validated['poll_options'] ?? null);

        if ($pollOptions === false) {
            abort(422, 'A poll must have between 2 and 4 non-empty options of at most 40 characters.');
        }

        $this->ensureCanPublish->handle($user, 1 + count($threadPosts));

        $channelId = $this->resolveChannelId(
            $user,
            $validated['channel_id'] ?? null,
            isset($validated['channel_name']) && is_string($validated['channel_name']) ? mb_trim($validated['channel_name']) : null,
        );

        /** @var array<int, array<string, mixed>> $payloads */
        $payloads = [[
            'to_id' => $user->id,
            'content' => '__UPDATE__',
            'answer' => $validated['content'],
            'answer_created_at' => now(),
            'poll_expires_at' => $pollOptions !== [] ? now()->addDays((int) $validated['poll_duration']) : null,
            'channel_id' => $channelId,
        ]];

        foreach ($threadPosts as $index => $post) {
            $payloads[] = [
                'to_id' => $user->id,
                'content' => '__UPDATE__',
                'answer' => $post,
                'answer_created_at' => now(),
                'poll_expires_at' => ($threadPollOptions[$index] ?? []) !== []
                    ? now()->addDays($threadPollDurations[$index])
                    : null,
            ];
        }

        $questions = $this->createQuestion->handle(
            $user,
            $payloads,
            $pollOptions,
            $threadPollOptions,
            $channelId,
        );

        $created = (new FeedQuestion)(
            Question::query()->whereIn('id', collect($questions)->map->id->all()),
            $user->id,
        )->get()->keyBy('id');

        return collect($questions)
            ->map(fn (Question $question) => $created->get($question->id))
            ->filter()
            ->values();
    }

    /**
     * Normalize poll options, mirroring the web composer's rules
     * (2 to 4 non-empty options, 40 characters each).
     *
     * @return list<string>|false
     */
    private function cleanPollOptions(mixed $options): array|false
    {
        if ($options === null) {
            return [];
        }

        if (! is_array($options)) {
            return false;
        }

        $cleaned = collect($options)
            ->filter(fn (mixed $option): bool => is_string($option) && mb_trim($option) !== '')
            ->map(fn (string $option): string => mb_trim($option))
            ->values()
            ->all();

        if (count($cleaned) !== count($options) || count($cleaned) < 2 || count($cleaned) > 4) {
            return false;
        }

        foreach ($cleaned as $option) {
            if (mb_strlen($option) > 40) {
                return false;
            }
        }

        return $cleaned;
    }

    /**
     * Resolve the channel id, mirroring the web composer's staging:
     * a new channel name is created at publish; admin-only channels
     * are silently dropped for non-admins.
     */
    private function resolveChannelId(User $user, mixed $channelId, ?string $channelName): ?int
    {
        if (is_string($channelName) && $channelName !== '') {
            $slug = Str::slug($channelName);

            if (blank($slug)) {
                return null;
            }

            if (in_array($slug, Channel::ADMIN_ONLY_SLUGS, true) && ! $user->isAdmin()) {
                return null;
            }

            return $this->createChannel->handle($user, $channelName, $slug)->id;
        }

        if ($channelId === null) {
            return null;
        }

        $channel = Channel::query()->find($channelId);

        if (! $channel) {
            return null;
        }

        if (in_array($channel->slug, Channel::ADMIN_ONLY_SLUGS, true) && ! $user->isAdmin()) {
            return null;
        }

        return $channel->id;
    }
}
