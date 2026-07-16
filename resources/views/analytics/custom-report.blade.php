<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 dark:bg-slate-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    {{ __('Custom Report Builder') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Buat laporan metrik silang secara on-the-fly</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Metrik Utama -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-widest">Pilih Metrik</label>
                            <select class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-slate-500 focus:border-slate-500">
                                <option>Tingkat Kehadiran Harian</option>
                                <option>Rata-rata Nilai Ujian</option>
                                <option>Total Poin Pelanggaran</option>
                                <option>Tunggakan SPP (Rp)</option>
                            </select>
                        </div>
                        <!-- Group By -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-widest">Kelompokkan (Group By)</label>
                            <select class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-slate-500 focus:border-slate-500">
                                <option>Per Kelas</option>
                                <option>Per Tingkatan (X, XI, XII)</option>
                                <option>Per Bulan</option>
                                <option>Per Status Siswa</option>
                            </select>
                        </div>
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-widest">Rentang Waktu</label>
                            <select class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-slate-500 focus:border-slate-500">
                                <option>Semester Ganjil 2026/2027</option>
                                <option>Semester Genap 2026/2027</option>
                                <option>30 Hari Terakhir</option>
                                <option>1 Tahun Terakhir</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                        <button type="button" onclick="alert('Laporan sedang digenerate... (Tampilan UI Mock)')" class="px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Generate Laporan
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Result Placeholder -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-[2.5rem] p-12 border border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 text-gray-300 dark:text-gray-600 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-gray-500 dark:text-gray-400">Belum Ada Laporan</h4>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Silakan konfigurasi parameter di atas dan klik Generate untuk memuat data tabel interaktif.</p>
            </div>
        </div>
    </div>
</x-app-layout>
