<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Serves Apple's associated domains statement.
 */
final readonly class AppleAppLinksAssociationController
{
    /**
     * Return the app association when its identifiers are configured.
     */
    public function __invoke(): JsonResponse
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
