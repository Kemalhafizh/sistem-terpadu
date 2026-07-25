<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </span>
                    Horizontal Scaling Architecture Blueprint & Read Replicas
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Blueprint infrastruktur berskala besar: Read Replicas, Redis Session Cluster, Connection Pooling, & CDN.</p>
            </div>
            <a href="{{ route('scaling.configuration') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                Konfigurasi Cluster Node
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Read Replica Topology Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Topologi Topologi Database Read Replica</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pemisahan lalu lintas Read/Write untuk skalabilitas query.</p>
                    </div>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-mono text-xs font-bold rounded-xl uppercase">ACTIVE & SYNCHRONIZED</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-mono text-xs">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-indigo-200 space-y-1">
                        <span class="text-indigo-600 font-bold block">PRIMARY NODE (READ / WRITE)</span>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $replicaConfig['primary_node'] }}</p>
                        <p class="text-[10px] text-gray-400">Master Write Engine</p>
                    </div>

                    @foreach($replicaConfig['read_nodes'] as $node)
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-emerald-200 space-y-1">
                            <span class="text-emerald-600 font-bold block">READ REPLICA NODE (READ-ONLY)</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $node }}</p>
                            <p class="text-[10px] text-gray-400">Query Offloading Replica</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Session & Cache Externalization -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Redis Session Externalization</h4>
                    <div class="flex justify-between items-center font-mono text-xs p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                        <span>Driver:</span>
                        <span class="font-bold text-indigo-600 uppercase">{{ $sessionConfig['driver'] }}</span>
                    </div>
                    <div class="flex justify-between items-center font-mono text-xs p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                        <span>Redis Cluster:</span>
                        <span class="font-bold text-indigo-600">{{ $sessionConfig['redis_cluster'] }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Status Kesehatan Web Application Nodes</h4>
                    @foreach($health['web_nodes'] as $w)
                        <div class="flex justify-between items-center font-mono text-xs p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <span>{{ $w['name'] }}:</span>
                            <span class="font-bold text-emerald-600 uppercase">{{ strtoupper($w['status']) }} (Load {{ $w['load_avg'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
