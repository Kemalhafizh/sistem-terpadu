<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('uks.dashboard') }}" class="hover:text-teal-600 transition-colors">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Log Pemberian Obat') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • PEMBERIAN RESEP & SEDIAN OBAT</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center mb-6">
                    <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                    Riwayat Transaksi Pemberian Obat Siswa
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <th class="pb-3">Tanggal / Waktu</th>
                                <th class="pb-3">Nama Penerima (Siswa)</th>
                                <th class="pb-3">Nama Obat</th>
                                <th class="pb-3 text-center">Jumlah Diberikan</th>
                                <th class="pb-3 text-right">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                            @forelse($logs as $log)
                                <tr class="group hover:bg-gray-50/55 dark:hover:bg-gray-900/10 transition-all">
                                    <td class="py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y • H:i') }} WIB
                                    </td>
                                    <td class="py-4 text-sm font-extrabold text-gray-900 dark:text-white">
                                        {{ $log->student_name }}
                                    </td>
                                    <td class="py-4 text-sm font-bold text-teal-600 dark:text-teal-400">
                                        {{ $log->medicine_name }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-extrabold text-gray-800 dark:text-gray-200">
                                        {{ $log->quantity }} Pcs
                                    </td>
                                    <td class="py-4 text-right text-xs text-gray-400 dark:text-gray-500 font-semibold">
                                        Diberikan via Kunjungan UKS
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Belum ada riwayat pemberian obat terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
