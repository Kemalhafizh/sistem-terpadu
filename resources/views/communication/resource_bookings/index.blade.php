<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </span>
                    Resource Manager & Booking Aset Sekolah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem manajemen reservasi aset dan ruangan (Lab, Aula, Ruang Rapat, Masjid) untuk mencegah bentrok jadwal.</p>
            </div>
            <a href="{{ route('calendar.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Kembali ke Kalender
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Resource Reservation Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Formulir Pengajuan Booking Ruangan / Aset</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sistem otomatis memverifikasi ketersediaan dan mencegah bentrok waktu secara real-time.</p>
                </div>

                <form action="{{ route('resource_bookings.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Aset / Ruangan Target</label>
                        <select name="bookable_resource_id" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            @foreach($resources as $res)
                                <option value="{{ $res->id }}">{{ $res->name }} (Kapasitas: {{ $res->capacity ?? 'N/A' }} orang)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tujuan / Kepentingan Penggunaan</label>
                        <input type="text" name="purpose" required placeholder="Rapat Evaluasi Guru / Pelatihan CBT" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Mulai Penggunaan</label>
                        <input type="datetime-local" name="start_datetime" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="end_datetime" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Kirim Permohonan Reservasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Booking Log Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Pengajuan Reservasi Terbaru</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat reservasi ruangan dan persetujuan otorisasi admin.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Aset / Ruangan</th>
                                <th class="px-6 py-4">Pemohon (Booked By)</th>
                                <th class="px-6 py-4">Tujuan / Kepentingan</th>
                                <th class="px-6 py-4">Rentang Waktu</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Persetujuan Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($recentBookings as $b)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ optional($b->resource)->name }}</td>
                                    <td class="px-6 py-4 font-bold">{{ optional($b->user)->name }}</td>
                                    <td class="px-6 py-4">{{ $b->purpose }}</td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500">
                                        {{ $b->start_datetime->format('d M Y H:i') }} - {{ $b->end_datetime->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @switch($b->status)
                                            @case('approved')
                                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded">DISETUJUI</span>
                                                @break
                                            @case('rejected')
                                                <span class="px-2.5 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 font-mono text-[10px] font-bold rounded">DITOLAK</span>
                                                @break
                                            @default
                                                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 font-mono text-[10px] font-bold rounded">MENUNGGU</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if($b->status === 'pending' && auth()->user()->hasRole(['superadmin', 'admin']))
                                            <form action="{{ route('resource_bookings.approve', $b->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition-all shadow-sm">
                                                    Setujui
                                                </button>
                                            </form>

                                            <form action="{{ route('resource_bookings.reject', $b->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 hover:bg-rose-500/20 rounded-lg text-[11px] font-bold transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-[11px] font-mono">
                                                {{ $b->approver ? 'Oleh: ' . $b->approver->name : '-' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada riwayat reservasi aset sekolah.
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
