<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $impressions
 * @property int $hovers
 * @property int $clicks
 */
final class PanAnalytic extends Model
{
    /** @use HasFactory<\Database\Factories\PanAnalyticFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
