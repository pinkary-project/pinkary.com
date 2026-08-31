<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BlockedAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $created_at
 */
final class BlockedAccount extends Model
{
    /** @use HasFactory<BlockedAccountFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
