<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    Monitoring Kepatuhan Pembacaan SOP (Compliance Rate)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan persentase konfirmasi persetujuan dokumen SOP oleh seluruh staf institusi.</p>
            </div>
            <a href="{{ route('sop.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Katalog SOP
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Compliance Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Analisis Kepatuhan Konfirmasi SOP</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Persentase staf yang telah menyetujui dokumen kebijakan resmi.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Kode & Judul SOP</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Persentase Kepatuhan</th>
                                <th class="px-6 py-4">Staf Belum Menandatangani</th>
                                <th class="px-6 py-4 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($sops as $sop)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold block text-[11px]">{{ $sop->code }}</span>
                                        <span class="font-bold text-gray-900 dark:text-white block">{{ $sop->title }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] uppercase">{{ $sop->category }}</td>
                                    <td class="px-6 py-4 font-mono">
                                        <div class="flex items-center gap-3">
                                            <div class="w-24 bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                                <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $sop->compliance_rate }}%"></div>
                                            </div>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $sop->compliance_rate }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px]">
                                        {{ $sop->unacknowledged_users->count() }} Staf Belum Sign
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('sop.show', $sop->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Lihat SOP
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada dokumen SOP yang dipublikasikan.
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
