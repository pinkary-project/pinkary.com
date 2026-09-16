<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Question $resource */
final class QuestionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'content' => $this->resource->anonymously ? null : $this->resource->sharable_content,
            'answer' => $this->resource->sharable_answer,
            'anonymously' => $this->resource->anonymously,
            'views' => $this->resource->views,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => ($this->resource->answer_updated_at ?: $this->resource->answer_created_at)?->toIso8601String(),
            'from' => $this->author($this->resource->from),
            'to' => $this->author($this->resource->to),
            'metrics' => [
                'likes' => (int) ($this->resource->likes_count ?? 0),
                'comments' => (int) ($this->resource->children_count ?? 0),
                'liked' => (bool) ($this->resource->is_liked ?? false),
                'bookmarked' => (bool) ($this->resource->is_bookmarked ?? false),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function author(object $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar_url,
            'verified' => (bool) $user->is_verified,
            'company_verified' => (bool) $user->is_company_verified,
        ];
    }
}
