<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionComments;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class IndexController
{
    public function __invoke(PaginatedRequest $request, Question $question): AnonymousResourceCollection
    {
        $perPage = $request->validated()['per_page'] ?? 20;

        $comments = (new FeedQuestion)(
            Question::query()->where('parent_id', $question->id)->orderBy('created_at')->orderBy('id'),
            $request->user()?->id,
        )->simplePaginate($perPage);

        return QuestionResource::collection($comments);
    }
}
