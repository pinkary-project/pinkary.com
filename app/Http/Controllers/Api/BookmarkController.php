<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class BookmarkController
{
    /**
     * List the authenticated user's bookmarked questions, newest first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 50);
        $userId = $request->user()->id;

        $builder = Question::query()
            ->select('questions.id')
            ->join('bookmarks', 'bookmarks.question_id', '=', 'questions.id')
            ->where('bookmarks.user_id', $userId)
            ->orderByDesc('bookmarks.created_at');

        $paginator = (new FeedQuestion)($builder, $userId)->simplePaginate($perPage);

        return QuestionResource::collection($paginator);
    }
}
