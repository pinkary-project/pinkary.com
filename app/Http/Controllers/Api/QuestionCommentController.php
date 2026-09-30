<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Queries\Feeds\FeedQuestion;
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
}
