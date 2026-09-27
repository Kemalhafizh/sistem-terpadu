<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Services\Integration\GraphqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiGatewayController extends Controller
{
    /**
     * GraphQL Federation Endpoint (Coexistence Gateway).
     */
    public function graphqlEndpoint(Request $request, GraphqlService $graphqlService)
    {
        $query = $request->input('query', '');
        $variables = $request->input('variables', []);

        if (! $graphqlService->validateSchema($query)) {
            return response()->json([
                'errors' => [['message' => 'GraphQL query/mutation string is invalid or empty.']],
            ], 400);
        }

        if (str_contains($query, 'mutation')) {
            $result = $graphqlService->resolveMutation($query, $variables);
        } else {
            $result = $graphqlService->resolveQuery($query, $variables);
        }

        return response()->json($result);
    }

    /**
     * Display API Client Management View.
     */
    public function clientManagement()
    {
        $clients = ApiClient::with('creator')->orderBy('id', 'desc')->get();

        return view('integration.api.client-management', compact('clients'));
    }

    /**
     * Register new API Client credentials.
     */
    public function registerClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'rate_limit_per_minute' => 'required|integer|min:10|max:1000',
        ]);

        $client = ApiClient::create([
            'name' => $request->name,
            'client_id' => 'client_'.Str::random(12),
            'client_secret' => 'secret_'.Str::random(32),
            'allowed_scopes' => ['read', 'write'],
            'rate_limit_per_minute' => $request->rate_limit_per_minute,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', "API Client [{$client->name}] berhasil dibuat dengan Client ID [{$client->client_id}]!");
    }

    /**
     * View API Usage & Performance Stats.
     */
    public function apiUsageStats()
    {
        $clients = ApiClient::where('is_active', true)->get();

        return view('integration.api.usage-stats', compact('clients'));
    }
}
