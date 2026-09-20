<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Link $resource
 */
final class LinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource->user;

        return [
            'id' => $this->resource->id,
            'description' => $this->resource->description,
            'url' => $this->resource->url.(str_contains((string) $this->resource->url, '?') ? '&' : '?').'ref=pinkary',
            'click_count' => (int) $this->resource->click_count,
            'is_visible' => (bool) $this->resource->is_visible,
            'gradient' => $user?->gradient,
            'link_shape' => $user?->link_shape,
        ];
    }
}
