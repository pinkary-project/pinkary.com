<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class AppLinksAssociationController
{
    public function android(): JsonResponse
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

    public function apple(): JsonResponse
    {
        $teamId = config('services.app_links.ios_team_id');
        $bundleId = config('services.app_links.ios_bundle_id');

        if (! is_string($teamId) || preg_match('/\A[A-Z0-9]{10}\z/', $teamId) !== 1
            || ! is_string($bundleId) || preg_match('/\A[A-Za-z0-9.-]+\z/', $bundleId) !== 1) {
            return response()->json([], 503);
        }

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => [[
                    'appID' => $teamId.'.'.$bundleId,
                    'components' => [
                        ['/' => '/@*'],
                    ],
                ]],
            ],
        ])->header('Cache-Control', 'public, max-age=300');
    }
}
