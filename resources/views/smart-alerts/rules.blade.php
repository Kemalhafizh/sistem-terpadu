<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-teal-500/10 text-teal-500 rounded-xl"></span>
                    <span>{{ __('Aturan & Ambang Batas Smart Alert') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Sesuaikan parameter ambang batas yang memicu peringatan deteksi dini secara otomatis oleh sistem
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Navigation/Tabs Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md p-2 rounded-2xl border border-gray-100 dark:border-slate-800/80 shadow-sm">
            <div class="flex flex-wrap items-center gap-1">
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
                    <a href="{{ route('smart-alerts.dashboard') }}" 
                       class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
                        Dashboard
                    </a>
                @endif
                <a href="{{ route('smart-alerts.index') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
                    Alert Aktif
                </a>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
                    <a href="{{ route('smart-alerts.rules') }}" 
                       class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-teal-500 text-white shadow-lg shadow-teal-500/20">
                        Aturan Deteksi
                    </a>
                @endif
                <a href="{{ route('smart-alerts.history') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
                   ⏱ Riwayat
                </a>
            </div>
            
            <div class="flex items-center gap-2">
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
                    <a href="{{ route('smart-alerts.export') }}" 
                       class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 active:scale-95 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Unduh PDF</span>
                    </a>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk']))
                    <a href="{{ route('smart-alerts.create') }}" 
                       class="px-4 py-2 bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-600 hover:to-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-md shadow-teal-500/10 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Alert Baru</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Success Alert --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3 animate-fade-in shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="p-4 bg-rose-50/80 dark:bg-rose-950/30 backdrop-blur-sm border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 rounded-2xl shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-black uppercase tracking-wider">Periksa Kembali Input Anda:</span>
                </div>
                <ul class="list-disc list-inside text-xs font-semibold space-y-1 pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Configuration Form --}}
        <form action="{{ route('smart-alerts.rules.update') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Section 1: Akademik & Kehadiran Siswa --}}
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center space-x-2">
                    <span class="p-1 bg-indigo-500/10 text-indigo-500 rounded"></span>
                    <span>Akademik & Kehadiran Siswa</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- GPA Threshold --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition">
                        <div>
                            <label for="smart_alert_at_risk_gpa_threshold" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Rerata Nilai Minimum (GPA)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Batas bawah nilai rata-rata siswa sebelum sistem menandai status "Akademik Bermasalah" (At-Risk).
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" step="0.1" min="0" max="100" 
                                   name="smart_alert_at_risk_gpa_threshold" 
                                   id="smart_alert_at_risk_gpa_threshold" 
                                   value="{{ old('smart_alert_at_risk_gpa_threshold', $rules['smart_alert_at_risk_gpa_threshold']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Poin</span>
                        </div>
                    </div>

                    {{-- Attendance Threshold --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition">
                        <div>
                            <label for="smart_alert_at_risk_attendance_threshold" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Batas Kehadiran Minimum (%)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Persentase minimal absensi kehadiran siswa dalam semester berjalan sebelum memicu rekomendasi peringatan penurunan kehadiran.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" step="1" min="0" max="100" 
                                   name="smart_alert_at_risk_attendance_threshold" 
                                   id="smart_alert_at_risk_attendance_threshold" 
                                   value="{{ old('smart_alert_at_risk_attendance_threshold', $rules['smart_alert_at_risk_attendance_threshold']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Poin Pelanggaran Bimbingan Konseling (BK) --}}
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center space-x-2">
                    <span class="p-1 bg-rose-500/10 text-rose-500 rounded"></span>
                    <span>Poin Pelanggaran BK Siswa</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- BK Warning Threshold --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition border-l-4 border-l-amber-500">
                        <div>
                            <label for="smart_alert_at_risk_bk_points_threshold" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Poin BK Kategori Peringatan (Warning)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Jumlah akumulasi poin pelanggaran tata tertib siswa untuk memicu alert status "Warning" guna pembinaan wali kelas.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_at_risk_bk_points_threshold" 
                                   id="smart_alert_at_risk_bk_points_threshold" 
                                   value="{{ old('smart_alert_at_risk_bk_points_threshold', $rules['smart_alert_at_risk_bk_points_threshold']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Poin</span>
                        </div>
                    </div>

                    {{-- BK Critical Threshold --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition border-l-4 border-l-rose-500">
                        <div>
                            <label for="smart_alert_at_risk_bk_points_critical_threshold" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Poin BK Kategori Kritis (Critical)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Batas kritis akumulasi poin pelanggaran siswa. Memicu alert kritis dan pengiriman notifikasi instan via WhatsApp ke BK & Kepala Sekolah.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_at_risk_bk_points_critical_threshold" 
                                   id="smart_alert_at_risk_bk_points_critical_threshold" 
                                   value="{{ old('smart_alert_at_risk_bk_points_critical_threshold', $rules['smart_alert_at_risk_bk_points_critical_threshold']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Poin</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Kinerja & Jurnal Mengajar Guru --}}
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center space-x-2">
                    <span class="p-1 bg-indigo-500/10 text-indigo-500 rounded"></span>
                    <span>Kinerja & Jurnal Mengajar Guru</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Journal Gap Days Warning --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition border-l-4 border-l-amber-500">
                        <div>
                            <label for="smart_alert_journal_gap_days" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Keterlambatan Jurnal Peringatan (Warning)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Jumlah hari kerja maksimal guru belum mengisi jurnal kelas/agenda mengajar sebelum ditandai status warning oleh sistem.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_journal_gap_days" 
                                   id="smart_alert_journal_gap_days" 
                                   value="{{ old('smart_alert_journal_gap_days', $rules['smart_alert_journal_gap_days']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Hari</span>
                        </div>
                    </div>

                    {{-- Journal Gap Days Critical --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition border-l-4 border-l-rose-500">
                        <div>
                            <label for="smart_alert_journal_gap_critical_days" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Keterlambatan Jurnal Kritis (Critical)
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Batas hari keterlambatan kritis pengisian jurnal kelas. Memicu alert kritis untuk dilaporkan langsung ke Waka Kurikulum.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_journal_gap_critical_days" 
                                   id="smart_alert_journal_gap_critical_days" 
                                   value="{{ old('smart_alert_journal_gap_critical_days', $rules['smart_alert_journal_gap_critical_days']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Hari</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: UKS & Aset Sarpras --}}
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center space-x-2">
                    <span class="p-1 bg-indigo-500/10 text-indigo-500 rounded"></span>
                    <span>UKS & Keterlambatan Pengembalian Aset</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Medicine Expiry Days --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition">
                        <div>
                            <label for="smart_alert_medicine_expiry_days" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Jendela Peringatan Kadaluarsa Obat UKS
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Jumlah hari sebelum tanggal kadaluarsa obat di UKS untuk memicu peringatan otomatis agar petugas UKS melakukan pembaruan stok.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_medicine_expiry_days" 
                                   id="smart_alert_medicine_expiry_days" 
                                   value="{{ old('smart_alert_medicine_expiry_days', $rules['smart_alert_medicine_expiry_days']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Hari</span>
                        </div>
                    </div>

                    {{-- Overdue Asset Days --}}
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 space-y-4 hover:shadow-lg transition border-l-4 border-l-amber-500">
                        <div>
                            <label for="smart_alert_overdue_asset_days" class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Keterlambatan Pengembalian Aset Sarpras
                            </label>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                Batas dispensasi keterlambatan peminjaman barang inventaris sekolah sebelum peminjam ditandai status warning.
                            </p>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number" min="0" 
                                   name="smart_alert_overdue_asset_days" 
                                   id="smart_alert_overdue_asset_days" 
                                   value="{{ old('smart_alert_overdue_asset_days', $rules['smart_alert_overdue_asset_days']) }}" 
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-bold pr-12">
                            <span class="absolute right-4 text-xs font-black text-gray-400 uppercase">Hari</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Submission Row --}}
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-slate-800">
                <a href="{{ route('smart-alerts.index') }}" 
                   class="px-6 py-3 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-200 active:scale-95">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-600 hover:to-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-lg shadow-teal-500/15">
                    Simpan Aturan Ambang Batas
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
