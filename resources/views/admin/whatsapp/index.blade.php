<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('WhatsApp Gateway & Simulator') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola konfigurasi API Fonnte, toggle fitur notifikasi, dan simulasikan pengiriman WhatsApp secara offline.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $settings['sandbox_mode'] === '1' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200' : 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border border-green-200' }}">
                    <span class="w-2 h-2 mr-1.5 rounded-full {{ $settings['sandbox_mode'] === '1' ? 'bg-amber-500 animate-pulse' : 'bg-green-500 animate-pulse' }}"></span>
                    {{ $settings['sandbox_mode'] === '1' ? 'Sandbox Active' : 'Production Active' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            
            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Logs</span>
                        <span class="text-xl font-black text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Terkirim</span>
                        <span class="text-xl font-black text-green-600 dark:text-green-400">{{ number_format($stats['sent']) }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Gagal</span>
                        <span class="text-xl font-black text-red-600 dark:text-red-400">{{ number_format($stats['failed']) }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pending</span>
                        <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['pending']) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Config & Test Panels -->
                <div class="space-y-6">
                    <!-- Config Panel -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider border-b border-gray-50 dark:border-gray-700 pb-3 mb-4">Pengaturan Gateway</h3>
                        
                        <form action="{{ route('admin.whatsapp.settings-update') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <!-- Sandbox Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="whatsapp_sandbox_mode" class="block text-xs font-bold text-gray-700 dark:text-gray-300">Sandbox Mode</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">Kirim offline gratis & aman.</span>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="whatsapp_sandbox_mode" name="whatsapp_sandbox_mode" value="1" class="sr-only peer" {{ $settings['sandbox_mode'] === '1' ? 'checked' : '' }}>
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- Fonnte Token -->
                            <div>
                                <label for="whatsapp_api_token" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Fonnte API Token</label>
                                <input type="password" id="whatsapp_api_token" name="whatsapp_api_token" value="{{ $settings['api_token'] }}" placeholder="••••••••••••••••" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Sender Phone -->
                            <div>
                                <label for="whatsapp_sender" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Pengirim (Device)</label>
                                <input type="text" id="whatsapp_sender" name="whatsapp_sender" value="{{ $settings['sender'] }}" placeholder="628xxxxxxxx" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <hr class="border-gray-50 dark:border-gray-700 my-2">

                            <!-- Feature Toggles -->
                            <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest my-2">Otomatisasi Pemicu</h4>

                            <!-- Attendance Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="whatsapp_enable_attendance" class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pemberitahuan Absensi Alpa</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">Kirim alert saat siswa alpa.</span>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="whatsapp_enable_attendance" name="whatsapp_enable_attendance" value="1" class="sr-only peer" {{ $settings['enable_attendance'] === '1' ? 'checked' : '' }}>
                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- BK Points Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="whatsapp_enable_point" class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pemberitahuan Poin BK</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">Kirim alert saat poin BK bertambah.</span>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="whatsapp_enable_point" name="whatsapp_enable_point" value="1" class="sr-only peer" {{ $settings['enable_point'] === '1' ? 'checked' : '' }}>
                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- Billing Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="whatsapp_enable_billing" class="block text-xs font-bold text-gray-700 dark:text-gray-300">Tagihan SPP & Iuran</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">Kirim alert pengingat bayar SPP.</span>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="whatsapp_enable_billing" name="whatsapp_enable_billing" value="1" class="sr-only peer" {{ $settings['enable_billing'] === '1' ? 'checked' : '' }}>
                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <button type="submit" class="w-full mt-2 inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm focus:outline-none">
                                Simpan Konfigurasi
                            </button>
                        </form>
                    </div>

                    <!-- Test Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider border-b border-gray-50 dark:border-gray-700 pb-3 mb-4">Uji Coba Simulator</h3>
                        
                        <form action="{{ route('admin.whatsapp.send-test') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="recipient" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Penerima</label>
                                <input type="text" id="recipient" name="recipient" required placeholder="08xxxxxxxxxx atau 628xxxxxxxx" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                <span class="text-[9px] text-gray-400 mt-1 block">Gunakan akhiran '999' atau kata 'failed' untuk mensimulasikan status gagal kirim.</span>
                            </div>

                            <div>
                                <label for="message" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Isi Pesan Uji</label>
                                <textarea id="message" name="message" rows="3" required placeholder="Tulis pesan pengujian..." class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500"></textarea>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm focus:outline-none">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                Kirim Pesan Simulasi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Audit Logs List (2 Columns Wide on large screens) -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Log Audit Aktivitas Komunikasi</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                        <th class="py-4 px-6">Tujuan</th>
                                        <th class="py-4 px-6">Tipe</th>
                                        <th class="py-4 px-6">Pesan</th>
                                        <th class="py-4 px-6">Status</th>
                                        <th class="py-4 px-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($logs as $log)
                                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                            <td class="py-4 px-6 font-bold text-gray-900 dark:text-white">
                                                {{ $log->recipient }}
                                                <span class="block text-[10px] text-gray-400 font-medium mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ str_replace('Notification', '', $log->type ?? 'Manual') }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 max-w-xs truncate text-gray-600 dark:text-gray-300 font-medium" title="{{ $log->message }}">
                                                {{ $log->message }}
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($log->status === 'sent')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/30">Terkirim</span>
                                                @elseif($log->status === 'failed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/30" title="{{ $log->error_message }}">Gagal</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">Tertunda</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <form action="{{ route('admin.whatsapp.resend', $log->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="p-1 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Kirim Ulang">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                                Belum ada aktivitas pengiriman log WhatsApp.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($logs->hasPages())
                            <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
