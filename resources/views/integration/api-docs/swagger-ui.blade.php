<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        </svg>
                    </span>
                    Swagger UI Interactive API Playground
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengujian interaktif endpoint REST & GraphQL Gateway API.</p>
            </div>
            <a href="{{ route('api_docs.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Portal Dokumentasi
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3 flex justify-between items-center">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Swagger UI Playground Console</h3>
                    <span class="font-mono text-xs text-indigo-600 font-bold">OpenAPI Spec URL: {{ $swaggerConfig['spec_url'] }}</span>
                </div>

                <div class="p-8 bg-slate-900 rounded-2xl text-white font-mono text-xs space-y-4">
                    <p class="text-emerald-400 font-bold">// Swagger UI Engine Live Embed Ready</p>
                    <p>GET {{ url('/api/v2/students') }} -> 200 OK</p>
                    <p>POST {{ url('/graphql') }} -> 200 OK (Query: { students { id name nisn } })</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
