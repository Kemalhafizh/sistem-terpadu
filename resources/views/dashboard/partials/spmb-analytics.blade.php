@php
    // Normalisasi $spmb_stats secara defensif untuk mencegah TypeError atau Cannot use object of type __PHP_Incomplete_Class as array
    if (isset($spmb_stats)) {
        if (is_object($spmb_stats) && get_class($spmb_stats) === '__PHP_Incomplete_Class') {
            $spmb_stats = [];
        } elseif ($spmb_stats instanceof \Illuminate\Support\Collection) {
            $spmb_stats = $spmb_stats->toArray();
        } elseif (!is_array($spmb_stats)) {
            $spmb_stats = [];
        }

        $spmb_stats = array_map(function($val) {
            return is_numeric($val) ? (int)$val : 0;
        }, (array) $spmb_stats);
    } else {
        $spmb_stats = [];
    }
@endphp
<div class="space-y-8 mt-10">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-black text-gray-800 flex items-center">
            <span class="w-8 h-8 bg-primary-900 text-white rounded-lg flex items-center justify-center mr-3 shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </span>
            Command Center SPMB (Penerimaan Siswa Baru)
        </h3>
        <a href="{{ route('spmb.admin.index') }}" class="text-xs font-bold text-primary-600 hover:underline">Kelola Pendaftaran Lengkap →</a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Total -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24 text-gray-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pendaftar</p>
            <h4 class="text-3xl font-black text-gray-800">
                {{ array_sum($spmb_stats) }}
            </h4>
        </div>

        <!-- Pending -->
        <div class="bg-amber-50 p-6 rounded-2xl shadow-sm border border-amber-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Menunggu Verifikasi</p>
            <h4 class="text-3xl font-black text-amber-700">{{ $spmb_stats['pending'] ?? 0 }}</h4>
        </div>

        <!-- Accepted -->
        <div class="bg-emerald-50 p-6 rounded-2xl shadow-sm border border-emerald-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Diterima (Siswa Baru)</p>
            <h4 class="text-3xl font-black text-emerald-700">{{ $spmb_stats['accepted'] ?? 0 }}</h4>
        </div>

        <!-- Rejected -->
        <div class="bg-rose-50 p-6 rounded-2xl shadow-sm border border-rose-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24 text-rose-600" fill="currentColor" viewBox="0 0 24 24"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Ditolak / Gugur</p>
            <h4 class="text-3xl font-black text-rose-700">{{ $spmb_stats['rejected'] ?? 0 }}</h4>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Trend Chart -->
        <div class="xl:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h4 class="text-sm font-black text-gray-800 mb-6 uppercase tracking-widest">Tren Pendaftaran (7 Hari Terakhir)</h4>
            <div class="h-64">
                <canvas id="spmbTrendChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h4 class="text-sm font-black text-gray-800 mb-6 uppercase tracking-widest">Aktivitas Terakhir</h4>
            <div class="space-y-4">
                @forelse($recent_registrations ?? [] as $reg)
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 transition-colors">
                                {{ substr($reg->full_name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-bold text-gray-800 truncate w-32">{{ $reg->full_name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $reg->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-tighter {{ $reg->status == 'pending' ? 'bg-amber-100 text-amber-700' : ($reg->status == 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $reg->status }}
                        </span>
                    </div>
                @empty
                    <p class="text-center py-10 text-xs text-gray-400 italic">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
