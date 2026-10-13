<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\FeedRequest;
use App\Http\Resources\QuestionResource;
use App\Queries\Feeds\FeedQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class FeedController
{
    /** Return a page of the requested feed tab. */
    public function index(FeedRequest $request, FeedQuery $feedQuery): AnonymousResourceCollection
    {
        $paginator = $feedQuery->paginate(
            $request->tab(),
            $request->perPage(),
            $request->user(),
        );

        return QuestionResource::collection($paginator);
    }
}
