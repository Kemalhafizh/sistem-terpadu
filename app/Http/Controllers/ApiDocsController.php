<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the Scribe API docs (`php artisan scribe:generate`). Replaces the
 * routes Scribe registers itself, which answered 500 whenever the generated
 * Postman collection / OpenAPI spec wasn't in storage yet.
 */
class ApiDocsController extends Controller
{
    public function index()
    {
        return view('scribe.index');
    }

    public function postman(): JsonResponse
    {
        return new JsonResponse($this->generatedFile('scribe/collection.json'), json: true);
    }

    public function openapi(): Response
    {
        $this->generatedFile('scribe/openapi.yaml');

        return response()->file(Storage::disk('local')->path('scribe/openapi.yaml'), ['Content-Type' => 'application/yaml']);
    }

    private function generatedFile(string $path): string
    {
        abort_unless(Storage::disk('local')->exists($path), 404, 'Dokumentasi API belum dibuat. Jalankan `php artisan scribe:generate`.');

        return Storage::disk('local')->get($path);
    }
}
