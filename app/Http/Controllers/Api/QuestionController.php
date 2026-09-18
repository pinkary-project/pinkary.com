<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Channels\CreateChannel;
use App\Actions\Questions\CreateBookmark;
use App\Actions\Questions\CreateLike;
use App\Actions\Questions\CreateQuestion;
use App\Actions\Questions\DeleteBookmark;
use App\Actions\Questions\DeleteLike;
use App\Actions\Questions\UpdatePollVote;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Requests\Api\StoreQuestionRequest;
use App\Http\Requests\Api\VotePollRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final readonly class QuestionController
{
    /**
     * Publish a shared update (thread) for the authenticated user.
     *
     * The main post plus up to 9 chained follow-ups are stored as
     * self-addressed questions, mirroring the web composer's threads.
     */
    public function store(StoreQuestionRequest $request, CreateQuestion $createQuestion, CreateChannel $createChannel): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        // Blank follow-ups are skipped, keeping each row's poll state
        // aligned with its post like the web composer does.
        /** @var list<string> $threadPosts */
        $threadPosts = [];
        /** @var array<int, list<string>> $filteredThreadPollOptions */
        $filteredThreadPollOptions = [];
        /** @var array<int, int> $filteredThreadPollDurations */
        $filteredThreadPollDurations = [];

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
                    return response()->json(['message' => 'Each thread poll needs 2 to 4 non-empty options and a duration of 1 to 7 days.'], 422);
                }

                $filteredThreadPollOptions[] = $options;
                $filteredThreadPollDurations[] = $duration;
            } else {
                $filteredThreadPollOptions[] = [];
                $filteredThreadPollDurations[] = 1;
            }

            $threadPosts[] = $post;
        }

        $pollOptions = $this->cleanPollOptions($validated['poll_options'] ?? null);

        if ($pollOptions === false) {
            return response()->json(['message' => 'A poll must have between 2 and 4 non-empty options of at most 40 characters.'], 422);
        }

        if ($limited = $this->rateLimited($user, 1 + count($threadPosts))) {
            return $limited;
        }

        $channelId = $this->resolveChannelId(
            $user,
            $createChannel,
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
                'poll_expires_at' => ($filteredThreadPollOptions[$index] ?? []) !== []
                    ? now()->addDays($filteredThreadPollDurations[$index])
                    : null,
            ];
        }

        $questions = $createQuestion->handle(
            $user,
            $payloads,
            $pollOptions,
            $filteredThreadPollOptions,
            $channelId,
        );

        // Reload with the feed's relations so the response carries
        // channel, poll, and author state.
        $created = (new FeedQuestion)(
            Question::query()->whereIn('id', collect($questions)->map->id->all()),
            $user->id,
        )->get()->keyBy('id');

        $ordered = collect($questions)
            ->map(fn (Question $question) => $created->get($question->id))
            ->filter()
            ->values();

        return QuestionResource::collection($ordered)->response()->setStatusCode(201);
    }

    /**
     * Show a single question with the same shape the feed returns,
     * plus its thread ancestors (root first) for connected display.
     */
    public function show(Request $request, Question $question): QuestionResource
    {
        $post = $this->hydrate($request, Question::query()->whereKey($question->id));

        return (new QuestionResource($post))->additional([
            'thread' => QuestionResource::collection($this->ancestors($request, $post)),
        ]);
    }

    /**
     * List a question's direct comments, oldest first.
     */
    public function comments(Request $request, Question $question): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 50);

        $comments = (new FeedQuestion)(
            Question::query()->where('parent_id', $question->id)->orderBy('created_at')->orderBy('id'),
            $request->user()?->id,
        )->simplePaginate($perPage);

        return QuestionResource::collection($comments);
    }

    /**
     * Comment on a question for the authenticated user.
     *
     * Comments are self-addressed questions linked through parent/root ids,
     * mirroring the web composer's reply flow.
     */
    public function storeComment(StoreCommentRequest $request, Question $question): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        if ($limited = $this->rateLimited($user)) {
            return $limited;
        }

        $comment = $user->questionsSent()->create([
            'to_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $question->id,
            'root_id' => $question->root_id ?? $question->id,
        ]);

        return (new QuestionResource($this->hydrate($request, Question::query()->whereKey($comment->id))))->response()->setStatusCode(201);
    }

    /**
     * Like the question for the authenticated user (idempotent).
     */
    public function like(Request $request, Question $question, CreateLike $createLike): JsonResponse
    {
        $createLike->handle($question, $request->user());

        return response()->json(['data' => [
            'liked' => true,
            'likes' => $question->likes()->count(),
        ]]);
    }

    /**
     * Remove the authenticated user's like (idempotent).
     */
    public function unlike(Request $request, Question $question, DeleteLike $deleteLike): JsonResponse
    {
        if ($like = $question->likes()->where('user_id', $request->user()->id)->first()) {
            $deleteLike->handle($like);
        }

        return response()->json(['data' => [
            'liked' => false,
            'likes' => $question->likes()->count(),
        ]]);
    }

    /**
     * Bookmark the question for the authenticated user (idempotent).
     */
    public function bookmark(Request $request, Question $question, CreateBookmark $createBookmark): JsonResponse
    {
        $createBookmark->handle($question, $request->user());

        return response()->json(['data' => [
            'bookmarked' => true,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }

    /**
     * Remove the authenticated user's bookmark (idempotent).
     */
    public function unbookmark(Request $request, Question $question, DeleteBookmark $deleteBookmark): JsonResponse
    {
        if ($bookmark = $question->bookmarks()->where('user_id', $request->user()->id)->first()) {
            $deleteBookmark->handle($bookmark);
        }

        return response()->json(['data' => [
            'bookmarked' => false,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }

    /**
     * Vote in the question's poll (toggling — voting the same option
     * again removes the vote, mirroring the web poll component).
     */
    public function votePoll(VotePollRequest $request, Question $question, UpdatePollVote $updatePollVote): JsonResponse
    {
        if ($question->poll_expires_at === null) {
            return response()->json(['message' => 'This question is not a poll.'], 422);
        }

        if ($question->isPollExpired()) {
            return response()->json(['message' => 'This poll has expired and voting is no longer allowed.'], 422);
        }

        $validated = $request->validated();

        $option = $question->pollOptions()->whereKey($validated['option_id'])->first();

        if (! $option) {
            return response()->json(['message' => 'The selected option is invalid.'], 422);
        }

        $updatePollVote->handle($request->user(), $question, $option);

        $fresh = $this->hydrate($request, Question::query()->whereKey($question->id));

        return response()->json(['data' => (new QuestionResource($fresh))->poll()]);
    }

    /**
     * Load a single question with the feed's columns, relations,
     * and per-user like/bookmark state.
     *
     * @param  Builder<Question>  $query
     */
    private function hydrate(Request $request, Builder $query): Question
    {
        return (new FeedQuestion)($query, $request->user()?->id)->firstOrFail();
    }

    /**
     * Walk the parent chain upward and return the hydrated ancestors,
     * oldest first, so clients can render the connected thread.
     *
     * @return \Illuminate\Support\Collection<int, Question>
     */
    private function ancestors(Request $request, Question $question): \Illuminate\Support\Collection
    {
        $ids = $question->ancestorIds();

        if ($ids->isEmpty()) {
            return collect();
        }

        $hydrated = (new FeedQuestion)(
            Question::query()->whereIn('id', $ids->all()),
            $request->user()?->id,
        )->get()->keyBy('id');

        return $ids
            ->map(fn (string $id) => $hydrated->get($id))
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
    private function resolveChannelId(User $user, CreateChannel $createChannel, mixed $channelId, ?string $channelName): ?int
    {
        if (is_string($channelName) && $channelName !== '') {
            $slug = Str::slug($channelName);

            if (blank($slug)) {
                return null;
            }

            if (in_array($slug, Channel::ADMIN_ONLY_SLUGS, true) && ! $user->isAdmin()) {
                return null;
            }

            return $createChannel->handle($user, $channelName, $slug)->id;
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

    /**
     * The 429 response when the user hit the publishing limits, if any.
     */
    private function rateLimited(User $user, int $incoming = 1): ?JsonResponse
    {
        if (app()->isLocal()) {
            return null;
        }

        if ($user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            return response()->json(['message' => 'You can only send 3 questions per minute.'], 429);
        }

        if ($user->questionsSent()->where('created_at', '>=', now()->subDay())->count() + $incoming > 30) {
            return response()->json(['message' => 'You can only send 30 questions per day.'], 429);
        }

        return null;
    }
}
