<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SearchRequest;
use App\Models\Hashtag;
use App\Models\User;
use App\Support\AbsoluteUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final readonly class SearchController
{
    /**
     * Search verified users and hashtags by prefix — the same sources
     * the web autocomplete draws from.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = mb_trim($validated['q'], "@# \t\n\r\0\x0B");

        if ($query === '') {
            return response()->json(['data' => ['users' => [], 'hashtags' => []]]);
        }

        $user = $request->user();

        $users = User::query()
            ->whereKeyNot($user->id)
            ->whereNotNull('email_verified_at')
            ->where(fn (Builder $grouped): Builder => $grouped
                ->where('name', 'like', "{$query}%")
                ->orWhere('username', 'like', "{$query}%")
            )
            ->withCount('followers')
            ->withExists([
                'followers as is_followed_by_user' => fn (Builder $follower): Builder => $follower
                    ->where('follower_id', '=', $user->id),
            ])
            ->orderByDesc('is_followed_by_user')
            ->orderByDesc('followers_count')
            ->orderBy('username')
            ->limit(10)
            ->get()
            ->map(fn (User $found): array => [
                'id' => $found->id,
                'name' => $found->name,
                'username' => $found->username,
                'avatar' => AbsoluteUrl::for($found->avatar_url, $request),
                'verified' => (bool) $found->is_verified,
                'company_verified' => (bool) $found->is_company_verified,
                'followed' => (bool) $found->is_followed_by_user,
            ])
            ->all();

        $hashtags = Hashtag::query()
            ->withCount('questions')
            ->where(DB::raw('LOWER(name)'), 'like', mb_strtolower("{$query}%"))
            ->orderByDesc('questions_count')
            ->limit(8)
            ->get()
            ->unique(fn (Hashtag $hashtag): string => mb_strtolower($hashtag->name))
            ->map(fn (Hashtag $hashtag): array => [
                'id' => $hashtag->id,
                'name' => $hashtag->name,
                'questions_count' => (int) $hashtag->questions_count,
            ])
            ->all();

        return response()->json(['data' => [
            'users' => $users,
            'hashtags' => array_values($hashtags),
        ]]);
    }
}
