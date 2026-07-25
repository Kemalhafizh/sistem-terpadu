<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </span>
                    Public API Documentation Portal (OpenAPI 3.0 Standard)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Portal dokumentasi terbuka interaktif REST API v2 dan GraphQL API Federation Endpoint.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('api_docs.swagger_ui') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    </svg>
                    Buka Swagger UI Playground
                </a>
                <a href="{{ route('api_docs.postman') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    Ekspor Postman Collection
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Overview Spec Card -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 rounded-3xl text-white shadow-xl space-y-4 border border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[10px] font-bold uppercase rounded-xl">
                        OPENAPI SPECIFICATION 3.0.3
                    </span>
                    <a href="{{ route('api_docs.openapi_json') }}" target="_blank" class="text-xs font-mono text-indigo-400 hover:underline">Unduh Raw openapi.json</a>
                </div>

                <h3 class="text-2xl font-black">{{ $spec['info']['title'] }}</h3>
                <p class="text-xs text-gray-300 leading-relaxed">{{ $spec['info']['description'] }}</p>

                <div class="pt-2 flex flex-wrap gap-4 font-mono text-xs text-gray-400 border-t border-slate-800">
                    <p>Base Server REST: <span class="text-emerald-400 font-bold">{{ url('/api/v2') }}</span></p>
                    <p>Base Server GraphQL: <span class="text-emerald-400 font-bold">{{ url('/graphql') }}</span></p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
