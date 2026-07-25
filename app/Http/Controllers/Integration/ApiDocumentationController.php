<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Services\Integration\ApiDocumentationService;

class ApiDocumentationController extends Controller
{
    /**
     * Display Public API Documentation Portal.
     */
    public function index(ApiDocumentationService $docService)
    {
        $spec = $docService->generateOpenApiSpec();

        return view('integration.api-docs.index', compact('spec'));
    }

    /**
     * Return OpenAPI 3.0 JSON specification payload.
     */
    public function openApiJson(ApiDocumentationService $docService)
    {
        return response()->json($docService->generateOpenApiSpec());
    }

    /**
     * Display Swagger UI embedded playground.
     */
    public function swaggerUi(ApiDocumentationService $docService)
    {
        $swaggerConfig = $docService->renderSwaggerUi();

        return view('integration.api-docs.swagger-ui', compact('swaggerConfig'));
    }

    /**
     * Export Postman Collection v2.1 JSON file.
     */
    public function postmanExport(ApiDocumentationService $docService)
    {
        $collection = $docService->generatePostmanCollection();

        return response()->json($collection, 200, [
            'Content-Disposition' => 'attachment; filename="sista-api-collection.json"',
        ]);
    }
}
