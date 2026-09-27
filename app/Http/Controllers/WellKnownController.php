<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Class WellKnownController
 *
 * Serves standardized domain verification manifests (RFC 5785)
 * for Android App Links and Apple Universal Links (AASA).
 */
class WellKnownController extends Controller
{
    /**
     * Digital Asset Links for Android App Links.
     * Accessible via https://domain/.well-known/assetlinks.json
     */
    public function assetLinks(): JsonResponse
    {
        $manifest = [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => 'sch.id.sultanagung1.sista',
                    'sha256_cert_fingerprints' => [
                        '14:6D:E9:7C:15:3F:B4:72:0D:37:A6:2A:4B:9B:9D:69:B5:12:34:56:78:90:AB:CD:EF:12:34:56:78:90:AB:CD',
                    ],
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }

    /**
     * Apple App Site Association for iOS Universal Links (AASA).
     * Accessible via https://domain/.well-known/apple-app-site-association
     */
    public function appleAppSiteAssociation(): JsonResponse
    {
        $manifest = [
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appID' => 'YBWSA12345.sch.id.sultanagung1.sista',
                        'paths' => [
                            '/pay/*',
                            '/exam/*',
                            '/tahfidz/*',
                            '/feed/*',
                            '/report/*',
                        ],
                    ],
                ],
            ],
            'webcredentials' => [
                'apps' => ['YBWSA12345.sch.id.sultanagung1.sista'],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }
}
