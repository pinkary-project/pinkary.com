<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\UserQuestions;

use App\Actions\Questions\GetUserQuestions;
use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class IndexController
{
    public function __invoke(PaginatedRequest $request, User $user, GetUserQuestions $getUserQuestions): AnonymousResourceCollection
    {
        $paginator = $getUserQuestions->handle(
            $user,
            $request->user(),
            (int) ($request->validated()['per_page'] ?? 20),
        );

        return QuestionResource::collection($paginator);
    }
}
