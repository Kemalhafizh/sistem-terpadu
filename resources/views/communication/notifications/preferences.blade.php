<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </span>
                    Pengaturan Preferensi Saluran Notifikasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Konfigurasi saluran komunikasi pengiriman pesan per kategori event.</p>
            </div>
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Inbox
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <form action="{{ route('notifications.preferences.update') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-6 p-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Matriks Saluran Notifikasi Per Kategori</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih saluran aktif (In-App Bell, Email, WhatsApp, Push Notification) untuk setiap kategori pesan.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="py-4 px-6">Kategori Pesan</th>
                                    <th class="py-4 px-6 text-center">In-App Bell</th>
                                    <th class="py-4 px-6 text-center">Email</th>
                                    <th class="py-4 px-6 text-center">WhatsApp</th>
                                    <th class="py-4 px-6 text-center">Push Notification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @foreach($preferences as $pref)
                                    <tr>
                                        <td class="py-4 px-6">
                                            <span class="font-bold text-gray-900 dark:text-white uppercase font-mono text-xs block">
                                                {{ $pref->event_category }}
                                            </span>
                                            <span class="text-[11px] text-gray-400">
                                                @switch($pref->event_category)
                                                    @case('academic') Pengumuman nilai, jadwal ujian, & tugas sekolah @break
                                                    @case('finance') Tagihan SPP, pembayaran, & kwitansi digital @break
                                                    @case('discipline') Tiket BK, sanksi kedisiplinan, & mutaba'ah @break
                                                    @case('hr') Gaji payroll, absensi pegawai, & surat keputusan @break
                                                    @default Pemberitahuan keamanan & pemeliharaan sistem @break
                                                @endswitch
                                            </span>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <input type="checkbox" name="preferences[{{ $pref->event_category }}][inapp]" value="1" {{ $pref->channel_inapp ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <input type="checkbox" name="preferences[{{ $pref->event_category }}][email]" value="1" {{ $pref->channel_email ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <input type="checkbox" name="preferences[{{ $pref->event_category }}][whatsapp]" value="1" {{ $pref->channel_whatsapp ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <input type="checkbox" name="preferences[{{ $pref->event_category }}][push]" value="1" {{ $pref->channel_push ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Simpan Perubahan Preferensi
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
