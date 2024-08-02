<?php

declare(strict_types=1);

/**
 * Configuration to tune the feed algorithms.
 */

return [
    'trending' => [
        'likes_bias' => env('TRENDING_LIKES_BIAS', 1),
        'comments_bias' => env('TRENDING_COMMENTS_BIAS', 1),
        'time_bias' => env('TRENDING_TIME_BIAS', 86400),
        'max_days_since_posted' => env('TRENDING_MAX_DAYS', 7),
    ],
];
