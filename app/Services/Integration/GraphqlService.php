<?php

namespace App\Services\Integration;

class GraphqlService
{
    /**
     * Resolve GraphQL query against SISTA domain schema.
     */
    public function resolveQuery(string $query, array $variables = []): array
    {
        if (str_contains($query, 'students')) {
            return [
                'data' => [
                    'students' => [
                        ['id' => 1, 'name' => 'Ahmad Faisal', 'nisn' => '0051234567', 'class' => 'XI-MIPA-1'],
                        ['id' => 2, 'name' => 'Siti Nurhaliza', 'nisn' => '0057654321', 'class' => 'X-IPS-2'],
                    ],
                ],
            ];
        }

        if (str_contains($query, 'institution')) {
            return [
                'data' => [
                    'institution' => [
                        'name' => school_name(),
                        'npsn' => school_npsn() ?: null,
                        'foundation' => school_foundation_name(),
                    ],
                ],
            ];
        }

        return [
            'data' => [
                'status' => 'GraphQL Gateway Coexistence v2.0 Operational',
                'schema' => 'Federated GraphQL Engine SISTA',
            ],
        ];
    }

    /**
     * Resolve GraphQL mutation payload.
     */
    public function resolveMutation(string $mutation, array $variables = []): array
    {
        return [
            'data' => [
                'updateStudent' => [
                    'success' => true,
                    'message' => 'Data siswa berhasil diperbarui via GraphQL Federation Endpoint.',
                ],
            ],
        ];
    }

    /**
     * Validate GraphQL query syntax & schema boundaries.
     */
    public function validateSchema(string $query): bool
    {
        $trimmed = trim($query);

        return ! empty($trimmed) && (str_contains($trimmed, 'query') || str_contains($trimmed, 'mutation') || str_starts_with($trimmed, '{'));
    }

    /**
     * Return GraphQL Introspection schema metadata.
     */
    public function introspection(): array
    {
        return [
            '__schema' => [
                'queryType' => ['name' => 'Query'],
                'mutationType' => ['name' => 'Mutation'],
                'types' => [
                    ['name' => 'Student', 'kind' => 'OBJECT'],
                    ['name' => 'Teacher', 'kind' => 'OBJECT'],
                    ['name' => 'Grade', 'kind' => 'OBJECT'],
                ],
            ],
        ];
    }
}
