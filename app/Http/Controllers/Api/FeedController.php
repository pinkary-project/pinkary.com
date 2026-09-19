<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Feeds\GetFeed;
use App\Http\Requests\Api\FeedRequest;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class FeedController
{
    public function index(FeedRequest $request, GetFeed $getFeed): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $paginator = $getFeed->handle(
            $validated['tab'] ?? 'recent',
            (int) ($validated['per_page'] ?? 20),
            $request->user(),
        );

        return QuestionResource::collection($paginator);
    }
}
