<?php

declare(strict_types=1);

test('Android app links are unavailable until a signing fingerprint is configured', function (): void {
    config()->set('services.app_links.android_sha256_fingerprints', null);

    $this->get(route('app-links.android'))
        ->assertStatus(503)
        ->assertExactJson([]);
});

test('Android app links return the configured package and signing fingerprints', function (): void {
    $fingerprint = implode(':', array_fill(0, 32, 'ab'));

    config()->set('services.app_links.android_package', 'org.pinkaryproject.mobile');
    config()->set('services.app_links.android_sha256_fingerprints', $fingerprint.',invalid');

    $this->get(route('app-links.android'))
        ->assertOk()
        ->assertExactJson([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => 'org.pinkaryproject.mobile',
                'sha256_cert_fingerprints' => [mb_strtoupper($fingerprint)],
            ],
        ]]);
});

test('Apple app links are unavailable until the app identifiers are configured', function (): void {
    config()->set('services.app_links.ios_team_id', null);

    $this->get(route('app-links.apple'))
        ->assertStatus(503)
        ->assertExactJson([]);
});

test('Apple app links return the configured app identifier and profile path', function (): void {
    config()->set('services.app_links.ios_team_id', 'A1B2C3D4E5');
    config()->set('services.app_links.ios_bundle_id', 'org.pinkaryproject.mobile');

    $this->get(route('app-links.apple'))
        ->assertOk()
        ->assertExactJson([
            'applinks' => [
                'apps' => [],
                'details' => [[
                    'appID' => 'A1B2C3D4E5.org.pinkaryproject.mobile',
                    'components' => [
                        ['/' => '/@*'],
                    ],
                ]],
            ],
        ]);
});
