<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SearchRequest;
use App\Services\Autocomplete\Types\Hashtags;
use App\Services\Autocomplete\Types\Mentions;
use App\Support\AbsoluteUrl;
use Illuminate\Http\JsonResponse;

final readonly class SearchController
{
    /**
     * Search verified users and hashtags by prefix — the same sources
     * the web autocomplete draws from.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $query = mb_trim($request->validated('q'), "@# \t\n\r\0\x0B");

        if ($query === '') {
            return response()->json(['data' => ['users' => [], 'hashtags' => []]]);
        }

        $userId = $request->user()->id;

        $users = (new Mentions)->search($query, $userId)
            ->map(fn ($result): array => [
                'id' => $result->id,
                'name' => $result->payload['name'],
                'username' => mb_ltrim($result->replacement, '@'),
                'avatar' => AbsoluteUrl::for($result->payload['avatarSrc'], $request),
                'verified' => (bool) $result->payload['isVerified'],
                'company_verified' => (bool) $result->payload['isCompanyVerified'],
                'followed' => (bool) $result->payload['isFollowedByUser'],
            ])
            ->all();

        $hashtags = (new Hashtags)->search($query)
            ->map(fn ($result): array => [
                'id' => $result->id,
                'name' => mb_ltrim($result->replacement, '#'),
            ])
            ->all();

        return response()->json(['data' => [
            'users' => $users,
            'hashtags' => array_values($hashtags),
        ]]);
    }
}
