<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\EnsureCanPublish;
use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Questions\ThreadedQuestionQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionCommentController
{
    /**
     * List a post's comments. Guests read them like the web, which only
     * renders comments inside the gated post page.
     */
    public function index(PaginatedRequest $request, Question $question): AnonymousResourceCollection
    {
        Gate::authorize('view', $question);

        $perPage = $request->perPage();

        $comments = (new FeedQuestion)(
            Question::query()
                ->where('parent_id', $question->id)
                ->tap(new WhereNotModerated)
                ->orderBy('created_at')
                ->orderBy('id'),
            $request->user()?->id,
        )->simplePaginate($perPage);

        return QuestionResource::collection($comments);
    }

    /** Comment on a post. */
    public function store(
        StoreCommentRequest $request,
        Question $question,
        ThreadedQuestionQuery $threaded,
        EnsureCanPublish $ensureCanPublish,
    ): JsonResponse {
        Gate::authorize('view', $question);

        /** @var User $user */
        $user = $request->user();

        $ensureCanPublish->handle($user);

        $comment = $user->questionsSent()->create([
            'to_id' => $user->id,
            // The reply goes in `answer`, not `content`, because
            // RecentQuestionsFeed skips questions whose answer is null.
            'content' => '__UPDATE__',
            'answer' => $request->validated('content'),
            'answer_created_at' => now(),
            'parent_id' => $question->id,
            'root_id' => $question->root_id ?? $question->id,
        ]);

        $thread = $threaded->get(Question::query()->findOrFail($comment->id), $user->id);

        return new QuestionResource($thread['question'])->response()->setStatusCode(201);
    }
}
