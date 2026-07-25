<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </span>
                    Enterprise Risk Register (ERM Dashboard)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Register analisis risiko institusi berdasarkan kategorisasi peluang (*likelihood*) dan dampak (*impact*).</p>
            </div>
            <a href="{{ route('risk.matrix') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Lihat Matriks Risiko 5x5 Heatmap
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Assessment Risk Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Identifikasi & Penilaian Risiko Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daftarkan ancaman potensial operasional, reputasi, keuangan, akademik, atau keselamatan.</p>
                </div>

                <form action="{{ route('risk.create') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Ancaman Risiko</label>
                        <input type="text" name="title" required value="Kerusakan Server Database Pusat Utama" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Risiko ERM</label>
                        <select name="category" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="IT/Cyber">IT / Cyber Security</option>
                            <option value="Academic">Akademik & Kurikulum</option>
                            <option value="Financial">Financial / Keuangan</option>
                            <option value="Safety">Safety & Keselamatan Fisik</option>
                            <option value="Reputational">Reputasi & Humas</option>
                            <option value="Regulatory">Regulasi & Hukum</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Peluang Kekejadian (Likelihood)</label>
                        <select name="likelihood" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="rare">Rare (1 - Jarang)</option>
                            <option value="unlikely">Unlikely (2 - Kecil)</option>
                            <option value="possible" selected>Possible (3 - Sedang)</option>
                            <option value="likely">Likely (4 - Besar)</option>
                            <option value="almost_certain">Almost Certain (5 - Sangat Tinggi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dampak Kerugian (Impact)</label>
                        <select name="impact" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="insignificant">Insignificant (1 - Sangat Kecil)</option>
                            <option value="minor">Minor (2 - Kecil)</option>
                            <option value="moderate" selected>Moderate (3 - Sedang)</option>
                            <option value="major">Major (4 - Besar)</option>
                            <option value="catastrophic">Catastrophic (5 - Bencana)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Mitigasi</label>
                        <select name="status" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="identified">Identified (Teridentifikasi)</option>
                            <option value="mitigating">Mitigating (Proses Penanganan)</option>
                            <option value="resolved">Resolved (Selesai/Terkendali)</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Risiko & Rencana Mitigasi</label>
                        <input type="text" name="description" required value="Potensi kehilangan data akademik akibat kegagalan perangkat keras server lokal." class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 mb-2">
                        <input type="text" name="mitigation_plan" placeholder="Rencana Mitigasi: Implementasi Replikasi Cloud Backup Otomatis Setiap Jam" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Simpan Ke Register Risiko
                        </button>
                    </div>
                </form>
            </div>

            <!-- Risk Register Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Register Penilaian Risiko Institusi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Daftar analisis tingkat keparahan risiko dan status mitigasi.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Judul Risiko</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Peluang x Dampak</th>
                                <th class="px-6 py-4">Skor & Level Risiko</th>
                                <th class="px-6 py-4">Pemilik Risiko</th>
                                <th class="px-6 py-4">Status Mitigasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($risks as $r)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                        {{ $r->title }}
                                        <p class="text-[11px] font-normal text-gray-500 mt-0.5">{{ $r->description }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] uppercase">{{ $r->category }}</td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500 capitalize">
                                        {{ $r->likelihood }} x {{ $r->impact }}
                                    </td>
                                    <td class="px-6 py-4 font-mono">
                                        @if($r->risk_level === 'Extreme')
                                            <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold rounded">
                                                {{ $r->risk_score }} — EXTREME
                                            </span>
                                        @elseif($r->risk_level === 'High')
                                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold rounded">
                                                {{ $r->risk_score }} — HIGH
                                            </span>
                                        @elseif($r->risk_level === 'Medium')
                                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 font-bold rounded">
                                                {{ $r->risk_score }} — MEDIUM
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold rounded">
                                                {{ $r->risk_score }} — LOW
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ optional($r->owner)->name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300 font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada data risiko terdaftar.
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
