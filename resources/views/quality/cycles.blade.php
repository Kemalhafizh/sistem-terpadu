<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </span>
                    Internal Quality Assurance (SPMI) & School Self-Evaluation (EDS)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Platform Evaluasi Diri Sekolah (EDS) digital berbasis siklus untuk pemetaan mutu dan rencana perbaikan berkelanjutan.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Create Cycle Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Buka Siklus Evaluasi Diri Sekolah (EDS) Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jadwalkan periode pengisian kuesioner evaluasi mutu internal untuk dewan guru dan pengelola.</p>
                </div>

                <form action="{{ route('quality.create_cycle') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran</label>
                        <input type="text" name="academic_year" required value="2025/2026" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Semester</label>
                        <select name="semester" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="genap">Genap</option>
                            <option value="ganjil">Ganjil</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Aktifkan Siklus EDS
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cycles List Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Siklus Evaluasi Diri Sekolah</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih siklus aktif untuk melakukan pengisian kuesioner atau melihat hasil mutu.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Tahun Ajaran & Semester</th>
                                <th class="px-6 py-4">Periode Pengisian</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Jumlah Respon</th>
                                <th class="px-6 py-4">Pembuat Siklus</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($cycles as $c)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                        TA {{ $c->academic_year }} Semester {{ ucfirst($c->semester) }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px]">
                                        {{ $c->start_date->format('d M Y') }} - {{ $c->end_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $c->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $c->responses->count() }} Isian
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ optional($c->creator)->name }}</td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('quality.fill_evaluation', $c->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Isi Kuesioner
                                        </a>
                                        <a href="{{ route('quality.results', $c->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Lihat Hasil Mutu
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada siklus evaluasi diri sekolah terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
