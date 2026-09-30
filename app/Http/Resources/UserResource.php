<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Link;
use App\Models\User;
use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property User $resource */
final class UserResource extends JsonResource
{
    /**
     * Overrides the request's viewer for the endpoints that hand back the
     * freshly authenticated user.
     *
     * Login and register answer before a token exists, so $request->user()
     * is null there and isMe() is false -- the client received its own
     * email and verification state as null, and could not tell an
     * unverified account to go verify it. Nothing else may set this.
     */
    private ?User $viewerOverride = null;

    /**
     * Render this user as seen by themselves.
     */
    public function viewingAs(User $viewer): static
    {
        $this->viewerOverride = $viewer;

        return $this;
    }

    /**
     * The user in the shape the clients render.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $this->viewerOverride ?? $request->user();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'username' => $this->resource->username,
            'bio' => $this->plainBio(),
            'avatar' => AbsoluteUrl::for($this->resource->avatar_url, $request),
            'email' => $this->isMe($viewer) ? $this->resource->email : null,
            'verification' => [
                'profile' => $this->resource->is_verified,
                'email' => $this->isMe($viewer) ? $this->resource->hasVerifiedEmail() : null,
                'company' => $this->resource->is_company_verified,
            ],
            'stats' => [
                // null, not 0, when the count was not loaded. The list
                // endpoints (followers, following, likers) never
                // withCount(), so a `?? 0` here reported a confident zero
                // for every row. The web renders no counts on those rows at
                // all, so nothing needed them -- but a wrong number is
                // worse than an absent one.
                'followers' => isset($this->resource->followers_count) ? (int) $this->resource->followers_count : null,
                'following' => isset($this->resource->following_count) ? (int) $this->resource->following_count : null,
                'posts' => isset($this->resource->posts_count) ? (int) $this->resource->posts_count : null,
                'views' => isset($this->resource->views) ? (int) $this->resource->views : null,
            ],
            'followed_by_me' => isset($this->resource->followed_by_me)
                ? (bool) $this->resource->followed_by_me
                : ($viewer instanceof User && (bool) $this->resource->followers()->where('follower_id', $viewer->id)->exists()),
            // The inverse flag, absent before. The web's follower,
            // following and liker rows all carry it (Livewire\Followers\
            // Index:44, Following\Index:46, Likes\Index:45) and render it
            // as the "Follows you" badge; without it a client cannot show
            // a mutual follow or offer Follow Back.
            'follows_me' => isset($this->resource->follows_me)
                ? (bool) $this->resource->follows_me
                : ($viewer instanceof User && (bool) $this->resource->following()->where('user_id', $viewer->id)->exists()),
            'is_me' => $this->isMe($viewer),
            'gradient' => $this->resource->gradient,
            'link_shape' => $this->resource->link_shape,
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

        return array_values($this->resource->links
            ->sortBy(fn (Link $link): int => ($index = array_search($link->id, $sort)) === false ? 1_000_000 + $link->id : $index)
            ->values()
            ->map(fn (Link $link): array => [
                'id' => $link->id,
                'description' => $link->description,
                'url' => $link->url.(str_contains($link->url, '?') ? '&' : '?').'ref=pinkary',
                'click_count' => (int) $link->click_count,
                'is_visible' => (bool) $link->is_visible,
                'gradient' => $this->resource->gradient,
                'link_shape' => $this->resource->link_shape,
            ])
            ->all());
    }
}
