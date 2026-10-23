<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Serves Android's Digital Asset Links statement.
 */
final readonly class AndroidAppLinksAssociationController
{
    /**
     * Return the app association when its signing certificate is configured.
     */
    public function __invoke(): JsonResponse
    {
        $packageName = config('services.app_links.android_package');
        $fingerprintValue = config('services.app_links.android_sha256_fingerprints');
        $fingerprints = is_string($fingerprintValue)
            ? array_values(array_filter(
                array_map(trim(...), explode(',', $fingerprintValue)),
                fn (string $fingerprint): bool => preg_match('/\A(?:[A-Fa-f0-9]{2}:){31}[A-Fa-f0-9]{2}\z/', $fingerprint) === 1,
            ))
            : [];

        if (! is_string($packageName) || $packageName === '' || $fingerprints === []) {
            return response()->json([], 503);
        }

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => $packageName,
                'sha256_cert_fingerprints' => array_map(Str::upper(...), $fingerprints),
            ],
        ]])->header('Cache-Control', 'public, max-age=300');
    }
}
