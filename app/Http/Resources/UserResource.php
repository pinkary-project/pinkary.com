<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property User $resource */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'username' => $this->resource->username,
            'bio' => $this->plainBio(),
            'avatar' => AbsoluteUrl::for($this->resource->avatar_url, $request),
            'email' => $this->isMe($viewer) ? $this->resource->email : null,            'verification' => [
                'profile' => $this->resource->is_verified,
                'email' => $this->isMe($viewer) ? $this->resource->hasVerifiedEmail() : null,
                'company' => $this->resource->is_company_verified,
            ],
            'stats' => [
                'followers' => (int) ($this->resource->followers_count ?? 0),
                'following' => (int) ($this->resource->following_count ?? 0),
                'posts' => (int) ($this->resource->posts_count ?? 0),
                'views' => (int) ($this->resource->views ?? 0),
            ],
            'followed_by_me' => $viewer instanceof User
                ? (bool) $this->resource->followers()->where('follower_id', $viewer->id)->exists()
                : false,
            'is_me' => $this->isMe($viewer),
            'links' => $this->links(),
            'member_since' => [
                'human' => $this->resource->created_at->diffForHumans(),
                'string' => $this->resource->created_at->toDateTimeLocalString(),
            ],
        ];
    }

    /**
     * Whether the viewer is looking at their own profile.
     */
    private function isMe(mixed $viewer): bool
    {
        return $viewer instanceof User && $viewer->is($this->resource);
    }

    /**
     * The rendered bio as plain text — the app cannot display HTML.
     */
    private function plainBio(): ?string
    {
        $bio = mb_trim(html_entity_decode(
            strip_tags((string) $this->resource->parsed_bio),
            ENT_QUOTES,
            'UTF-8'
        ));

        return $bio === '' ? null : $bio;
    }

    /**
     * Visible links in the owner's sort order, with the same ref marker
     * the web appends.
     *
     * @return list<array<string, mixed>>
     */
    private function links(): array
    {
        if (! $this->resource->relationLoaded('links')) {
            return [];
        }

        $sort = $this->resource->links_sort ?? [];

        return $this->resource->links
            ->sortBy(fn ($link): int => ($index = array_search($link->id, $sort)) === false ? 1_000_000 + $link->id : $index)
            ->values()
            ->map(fn ($link): array => [
                'id' => $link->id,
                'description' => $link->description,
                'url' => $link->url.(str_contains($link->url, '?') ? '&' : '?').'ref=pinkary',
            ])
            ->all();
    }
}
