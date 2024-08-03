<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $click_count
 * @property string $description
 * @property string $url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
final class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    /**
     * Get the user that owns the link.
     *
     * @return BelongsTo<User, Link>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
