<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl">🧠</span>
                    <span>{{ __('Smart Alerts & Deteksi Dini') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Engine analitik prediktif dan kecerdasan rekomendasi pencegahan risiko akademik & operasional
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{ 
            showResolveModal: false, 
            activeAlertId: null, 
            activeAlertTitle: '',
            resolutionNotes: ''
         }">
         
        {{-- Success Alert --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Live Notification Alert Banner --}}
        <div id="live-alert-banner" class="hidden p-4 bg-indigo-500/10 backdrop-blur-md border border-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-2xl flex items-center justify-between space-x-3 animate-pulse">
            <div class="flex items-center space-x-3">
                <span class="text-xl">🔔</span>
                <span class="text-sm font-black uppercase tracking-wider">Smart Alert Kritis Baru Terdeteksi Real-time!</span>
            </div>
            <button onclick="window.location.reload()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition">Reload Halaman</button>
        </div>

        {{-- Dasbor KPI Cerdas --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- KPI 1: Total Aktif --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Alert Aktif</span>
                <div class="flex items-end justify-between mt-2">
                    <span class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $stats['total_active'] }}</span>
                    <span class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl text-lg">💡</span>
                </div>
            </div>

            {{-- KPI 2: Kritis --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest">Kategori Kritis</span>
                <div class="flex items-end justify-between mt-2">
                    <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ $stats['critical'] }}</span>
                    <span class="p-2 bg-rose-500/10 text-rose-500 rounded-xl text-lg">🚨</span>
                </div>
            </div>

            {{-- KPI 3: Warning --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Peringatan (Warning)</span>
                <div class="flex items-end justify-between mt-2">
                    <span class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $stats['warning'] }}</span>
                    <span class="p-2 bg-amber-500/10 text-amber-500 rounded-xl text-lg">⚠️</span>
                </div>
            </div>

            {{-- KPI 4: Diketahui --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Dipantau (Acknowledged)</span>
                <div class="flex items-end justify-between mt-2">
                    <span class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $stats['acknowledged'] }}</span>
                    <span class="p-2 bg-blue-500/10 text-blue-500 rounded-xl text-lg">👁️</span>
                </div>
            </div>

            {{-- KPI 5: Diselesaikan --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300 col-span-2 lg:col-span-1">
                <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Terselesaikan (Resolved)</span>
                <div class="flex items-end justify-between mt-2">
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['resolved'] }}</span>
                    <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl text-lg">✅</span>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5">
            <form action="{{ route('smart-alerts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                {{-- Type Filter --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Kategori Alert</label>
                    <select name="type"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        <option value="">Semua Kategori</option>
                        <option value="at_risk_student" {{ request('type') === 'at_risk_student' ? 'selected' : '' }}>Siswa Berisiko Tinggi</option>
                        <option value="attendance_decline" {{ request('type') === 'attendance_decline' ? 'selected' : '' }}>Penurunan Kehadiran</option>
                        <option value="payment_default" {{ request('type') === 'payment_default' ? 'selected' : '' }}>Prediksi Gagal Bayar</option>
                        <option value="journal_gap" {{ request('type') === 'journal_gap' ? 'selected' : '' }}>Guru: Kesenjangan Jurnal</option>
                        <option value="critical_medicine" {{ request('type') === 'critical_medicine' ? 'selected' : '' }}>Kesehatan: Stok Kritis UKS</option>
                        <option value="overdue_asset" {{ request('type') === 'overdue_asset' ? 'selected' : '' }}>Sarpras: Keterlambatan Aset</option>
                    </select>
                </div>

                {{-- Severity Filter --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Tingkat Bahaya</label>
                    <select name="severity"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        <option value="">Semua Tingkat</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>🚨 Kritis (Critical)</option>
                        <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>⚠️ Peringatan (Warning)</option>
                        <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>ℹ️ Informasi</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Status Tindak Lanjut</label>
                    <select name="status"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        <option value="">Belum Selesai (Aktif/Pantau)</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Diketahui/Dipantau</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Terselesaikan</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center space-x-2">
                    <button type="submit"
                            class="flex-1 px-5 py-2.5 bg-gray-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-800 dark:hover:bg-white transition-all shadow-md active:scale-95">
                        Filter
                    </button>
                    @if(request()->hasAny(['type', 'severity', 'status']))
                        <a href="{{ route('smart-alerts.index') }}"
                           class="px-4 py-2.5 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 dark:hover:bg-slate-700 transition-all"
                           title="Reset filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Alerts List --}}
        <div class="space-y-4">
            @forelse($alerts as $alert)
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm border-l-4 
                     {{ $alert->severity === 'critical' ? 'border-l-rose-500 shadow-rose-500/5' : ($alert->severity === 'warning' ? 'border-l-amber-500 shadow-amber-500/5' : 'border-l-blue-500 shadow-blue-500/5') }}">
                    
                    {{-- Alert Details --}}
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Severity Badge --}}
                            @if($alert->severity === 'critical')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50">
                                    🚨 Kritis
                                </span>
                            @elseif($alert->severity === 'warning')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                    ⚠️ Peringatan
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50">
                                    ℹ️ Info
                                </span>
                            @endif

                            {{-- Category/Type Badge --}}
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">
                                {{ str_replace('_', ' ', $alert->type) }}
                            </span>

                            {{-- Status Badge --}}
                            @if($alert->status === 'active')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                                    Baru / Aktif
                                </span>
                            @elseif($alert->status === 'acknowledged')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                    Dipantau
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    Terselesaikan
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-black text-gray-900 dark:text-gray-100">
                            {{ $alert->title }}
                        </h3>

                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl">
                            {{ $alert->description }}
                        </p>

                        {{-- Metadata context info --}}
                        @if($alert->context_data)
                            <div class="mt-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-xl text-[11px] font-medium text-gray-500 dark:text-gray-400 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl border border-gray-100 dark:border-slate-800">
                                @if(isset($alert->context_data['classroom']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Kelas</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['classroom'] }}</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['average_grade']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Rata-Rata Nilas</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['average_grade'] }}</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['attendance_rate']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Persentase Kehadiran</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['attendance_rate'] }}%</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['bk_points']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Poin Pelanggaran BK</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['bk_points'] }} Poin</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['total_unpaid']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Total Tunggakan</span>
                                        <span class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($alert->context_data['total_unpaid'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['gap_days']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Terlambat Jurnal</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['gap_days'] }} Hari Kerja</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['stock']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Stok Obat UKS</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $alert->context_data['stock'] }} (Batas Min: {{ $alert->context_data['min_stock'] }})</span>
                                    </div>
                                @endif
                                @if(isset($alert->context_data['overdue_days']))
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400 mt-0.5">Terlambat Pengembalian</span>
                                        <span class="font-bold text-red-500">{{ $alert->context_data['overdue_days'] }} Hari</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Resolution Logs --}}
                        @if($alert->status === 'resolved' && $alert->resolution_notes)
                            <div class="mt-3 p-3 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                <div class="flex items-center space-x-1.5 mb-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                                    <span>✅ Solusi Tindak Lanjut:</span>
                                </div>
                                <p class="italic">"{{ $alert->resolution_notes }}"</p>
                                <p class="text-[9px] text-gray-400 dark:text-gray-500 mt-1">Diselesaikan oleh {{ $alert->resolvedBy->name ?? 'Sistem' }} pada {{ $alert->resolved_at->format('d-m-Y H:i') }}</p>
                            </div>
                        @endif

                        {{-- Acknowledged Logs --}}
                        @if($alert->status === 'acknowledged' && $alert->acknowledged_by)
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 font-semibold">
                                Dipantau oleh {{ $alert->acknowledgedBy->name ?? '-' }} sejak {{ $alert->acknowledged_at->format('d-m-Y H:i') }}
                            </p>
                        @endif
                    </div>

                    {{-- Actions block --}}
                    @if(in_array($alert->status, ['active', 'acknowledged']))
                        <div class="flex items-center gap-2 flex-shrink-0 self-end md:self-center">
                            {{-- Student Analytics Button (if alert is student-related) --}}
                            @if($alert->alertable_type === 'App\Models\Student')
                                <a href="{{ route('smart-alerts.student', $alert->alertable_id) }}" 
                                   class="px-4 py-2 border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded-xl text-xs font-black uppercase tracking-wider transition active:scale-95 flex items-center space-x-1"
                                   title="Buka Analisis Siswa">
                                    <span>🧠 Detail Siswa</span>
                                </a>
                            @endif

                            {{-- Acknowledge Button --}}
                            @if($alert->status === 'active')
                                <form action="{{ route('smart-alerts.acknowledge', $alert) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md shadow-blue-500/10 active:scale-95 flex items-center space-x-1">
                                        👁️ Pantau
                                    </button>
                                </form>
                            @endif

                            {{-- Resolve Button --}}
                            <button @click="showResolveModal = true; activeAlertId = {{ $alert->id }}; activeAlertTitle = '{{ addslashes($alert->title) }}'; resolutionNotes = ''" 
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md shadow-emerald-500/10 active:scale-95 flex items-center space-x-1">
                                ✅ Selesaikan
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                {{-- Empty State --}}
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5 border border-gray-100">
                            <span class="text-3xl">✨</span>
                        </div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Semua Aman & Kondusif</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                            Tidak ditemukan indikasi risiko atau anomali pada siswa, kepegawaian, maupun fasilitas UKS & Sarpras. SISTA berjalan optimal!
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($alerts->hasPages())
            <div class="mt-6">
                {{ $alerts->links() }}
            </div>
        @endif

        {{-- Premium Resolve Modal popup --}}
        <div x-show="showResolveModal" x-transition.opacity x-cloak
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
             
            <div @click.away="showResolveModal = false"
                 class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-100 dark:border-slate-800 max-w-md w-full p-6 space-y-6 shadow-2xl relative animate-scale-up"
                 x-data>
                 
                {{-- Modal Header --}}
                <div class="space-y-1">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                        <span class="p-1.5 bg-emerald-500/10 text-emerald-600 rounded-lg">✅</span>
                        <span>Selesaikan Masalah</span>
                    </h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="activeAlertTitle"></p>
                </div>

                {{-- Modal Form --}}
                <form :action="'/smart-alerts/' + activeAlertId + '/resolve'" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500">Catatan Penanganan & Tindak Lanjut</label>
                        <textarea name="resolution_notes" required rows="4" x-model="resolutionNotes"
                                  placeholder="Tuliskan tindakan nyata atau solusi yang telah dilakukan di lapangan (minimal 5 karakter)..."
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all font-medium leading-relaxed"></textarea>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="showResolveModal = false"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition active:scale-95">
                            Batal
                        </button>
                        <button type="submit" :disabled="resolutionNotes.trim().length < 5"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg shadow-emerald-500/15 active:scale-95">
                            Simpan Solusi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script to listen to Reverb WebSockets dynamically --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo !== 'undefined') {
                const userId = {{ auth()->id() }};
                window.Echo.private(`user.${userId}`)
                    .listen('.new-notification', (e) => {
                        console.log('Real-time Smart Alert received:', e);
                        if (e.notification && e.notification.type === 'smart_alert') {
                            const banner = document.getElementById('live-alert-banner');
                            if (banner) {
                                banner.classList.remove('hidden');
                            }
                        }
                    });
            }
        });
    </script>
</x-app-layout>
