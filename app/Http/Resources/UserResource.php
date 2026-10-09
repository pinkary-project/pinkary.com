<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Link;
use App\Models\User;
use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/** @property User $resource */
final class UserResource extends JsonResource
{
    /** Login and registration must supply the viewer before a token exists. */
    private ?User $viewerOverride = null;

    /** Render the resource as seen by $viewer rather than the request's user. */
    public function viewingAs(User $viewer): static
    {
        $this->viewerOverride = $viewer;

        return $this;
    }

    /**
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
            // Unloaded counts remain null rather than falsely reporting zero.
            'stats' => [
                'followers' => isset($this->resource->followers_count) ? (int) $this->resource->followers_count : null,
                'following' => isset($this->resource->following_count) ? (int) $this->resource->following_count : null,
                'posts' => isset($this->resource->posts_count) ? (int) $this->resource->posts_count : null,
                'views' => isset($this->resource->views) ? (int) $this->resource->views : null,
            ],
            'followed_by_me' => isset($this->resource->followed_by_me)
                ? (bool) $this->resource->followed_by_me
                : ($viewer instanceof User && (bool) $this->resource->followers()->where('follower_id', $viewer->id)->exists()),
            'follows_me' => isset($this->resource->follows_me)
                ? (bool) $this->resource->follows_me
                : ($viewer instanceof User && (bool) $this->resource->following()->where('user_id', $viewer->id)->exists()),
            'is_me' => $this->isMe($viewer),
            'can_ask_question' => $viewer instanceof User
                && $viewer->hasVerifiedEmail()
                && ! $this->isMe($viewer)
                && Gate::forUser($viewer)->allows('askQuestion', [
                    $this->resource,
                    isset($this->resource->follows_me) ? (bool) $this->resource->follows_me : null,
                ]),
            'question_preference' => $this->isMe($viewer) ? $this->resource->question_preference->value : null,
            'gradient' => $this->resource->gradient,
            'link_shape' => $this->resource->link_shape,
            'links' => $this->links(),
            'member_since' => [
                'human' => $this->resource->created_at->diffForHumans(),
                'string' => $this->resource->created_at->toDateTimeLocalString(),
            ],
        ];
    }

    /** Whether $viewer is the owner of this resource. */
    private function isMe(mixed $viewer): bool
    {
        return $viewer instanceof User && $viewer->is($this->resource);
    }

    /** Return the rendered bio as plain text. */
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
