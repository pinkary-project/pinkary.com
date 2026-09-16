<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuestionResource;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class FeedController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 50);
        $userId = $request->user()?->id;

        $questions = (new RecentQuestionsFeed)->builder()
            ->addSelect('questions.from_id', 'questions.to_id', 'questions.content', 'questions.answer', 'questions.anonymously', 'questions.views', 'questions.created_at', 'questions.answer_created_at', 'questions.answer_updated_at')
            ->with([
                'from:id,name,username,avatar,is_verified,is_company_verified',
                'to:id,name,username,avatar,is_verified,is_company_verified',
            ])
            ->withExists([
                'likes as is_liked' => fn ($query) => $query->when($userId, fn ($query) => $query->where('user_id', $userId)),
                'bookmarks as is_bookmarked' => fn ($query) => $query->when($userId, fn ($query) => $query->where('user_id', $userId)),
            ])
            ->withCount(['likes', 'children'])
            ->simplePaginate($perPage);

        return QuestionResource::collection($questions);
    }
}
