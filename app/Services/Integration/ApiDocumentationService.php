<?php

namespace App\Services\Integration;

class ApiDocumentationService
{
    /**
     * Generate OpenAPI 3.0 JSON specification payload.
     */
    public function generateOpenApiSpec(): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'SISTA Enterprise Core API',
                'description' => 'Dokumentasi Resmi Public REST & GraphQL API SMA Islam Sultan Agung 1 Semarang.',
                'version' => '2.0.0',
            ],
            'servers' => [
                ['url' => url('/api/v2'), 'description' => 'Production Gateway v2'],
                ['url' => url('/graphql'), 'description' => 'GraphQL Federation Endpoint'],
            ],
            'paths' => [
                '/students' => [
                    'get' => [
                        'summary' => 'Get Students Catalog',
                        'responses' => ['200' => ['description' => 'Successful response']],
                    ],
                ],
                '/graphql' => [
                    'post' => [
                        'summary' => 'GraphQL Federation Query & Mutation',
                        'responses' => ['200' => ['description' => 'GraphQL JSON response']],
                    ],
                ],
            ],
        ];
    }

    /**
     * Render Swagger UI embedded configuration payload.
     */
    public function renderSwaggerUi(): array
    {
        return [
            'spec_url' => route('api_docs.openapi_json'),
            'theme' => 'dark',
            'deepLinking' => true,
        ];
    }

    /**
     * Generate Postman Collection v2.1 JSON export.
     */
    public function generatePostmanCollection(): array
    {
        return [
            'info' => [
                'name' => 'SISTA Enterprise API Collection',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [
                [
                    'name' => 'Fetch Students',
                    'request' => [
                        'method' => 'GET',
                        'url' => url('/api/v2/students'),
                    ],
                ],
            ],
        ];
    }
}
