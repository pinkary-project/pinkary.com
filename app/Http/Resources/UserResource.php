<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property User $resource */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'username' => $this->resource->username,
            'bio' => $this->resource->bio,
            'email' => $this->resource->email,
            'avatar' => $this->resource->avatar,
            'verification' => [
                'profile' => $this->resource->is_verified,
                'email' => $this->resource->hasVerifiedEmail(),
                'company' => $this->resource->is_company_verified,
            ],
            'member_since' => [
                'human' => $this->resource->created_at->diffForHumans(),
                'string' => $this->resource->created_at->toDateTimeLocalString(),
            ],
        ];
    }
}
