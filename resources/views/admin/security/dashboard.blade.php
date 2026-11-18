@php
    $isLockdown = $isLockdown ?? false;
    $unresolvedCount = $unresolvedCount ?? 0;
    $blockedIpsCount = $blockedIpsCount ?? 0;
    $todayThreatsCount = $todayThreatsCount ?? 0;
    $recentThreats = $recentThreats ?? [];
    $blockedIps = $blockedIps ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-2xl border border-rose-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    Security Forensic Log & Intrusion Prevention Dashboard
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Monitor aktivitas forensik, pelacakan IP mencurigakan, dan kontrol darurat Lockdown System.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.security.logs') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs transition-all flex items-center gap-2 border border-gray-300 dark:border-gray-700">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Forensic Audit Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ lockdownModalOpen: false, blockIpModalOpen: false }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- System Lockdown Banner -->
            <div class="rounded-3xl p-6 border shadow-lg relative overflow-hidden transition-all duration-500 {{ $isLockdown ? 'bg-gradient-to-r from-rose-950 via-rose-900 to-rose-950 border-rose-500/50 text-white' : 'bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 border-gray-800 text-white' }}">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-2xl">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $isLockdown ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/50 animate-pulse' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                {{ $isLockdown ? 'LOCKDOWN SISTEM AKTIF' : 'Sistem Beroperasi Normal' }}
                            </span>
                        </div>
                        <h3 class="text-2xl font-black tracking-tight">
                            {{ $isLockdown ? 'SISTEM SISTA DALAM MODE LOCKDOWN DARURAT!' : 'Kontrol Pertahanan Darurat Intrusi (Intrusion Prevention)' }}
                        </h3>
                        <p class="text-xs text-gray-300 leading-relaxed">
                            {{ $isLockdown 
                                ? 'Seluruh akses publik dan rute non-administrator telah diblokir secara darurat. Hanya superadmin yang dapat mengelola rute keamanan ini.' 
                                : 'Gunakan tombol darurat Lockdown hanya jika terdeteksi adanya serbuan brute force, eksploitasi data nilai, atau indikasi peretasan sistem.' }}
                        </p>
                    </div>

                    <div>
                        <button @click="lockdownModalOpen = true" class="px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all duration-300 shadow-xl flex items-center gap-3 {{ $isLockdown ? 'bg-white text-rose-700 hover:bg-rose-50 shadow-white/10' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/30 border border-rose-500/30' }}">
                            <svg class="w-5 h-5 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            {{ $isLockdown ? 'NONAKTIFKAN LOCKDOWN' : 'AKTIFKAN DARURAT LOCKDOWN' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Lockdown</span>
                        <h4 class="text-xl font-black mt-1 {{ $isLockdown ? 'text-rose-500' : 'text-emerald-500' }}">
                            {{ $isLockdown ? 'AKTIF (SISTEM DIKUNCI)' : 'NON-AKTIF (NORMAL)' }}
                        </h4>
                    </div>
                    <div class="p-3 rounded-2xl {{ $isLockdown ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-500' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Ancaman Unresolved</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($unresolvedCount) }} Event</h4>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">IP Terblokir</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($blockedIpsCount) }} IP</h4>
                    </div>
                    <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-blue-500 uppercase tracking-wider">Log Hari Ini</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($todayThreatsCount) }} Event</h4>
                    </div>
                    <div class="p-3 bg-blue-500/10 text-blue-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Dual Split Grid Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Recent Threat Logs Table -->
                <div class="lg:col-span-7 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                            Aktivitas Ancaman Keamanan Terkini
                        </h3>
                        <a href="{{ route('admin.security.logs') }}" class="text-xs text-rose-600 dark:text-rose-400 font-bold hover:underline">Lihat Selengkapnya →</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                                <tr>
                                    <th class="p-3 rounded-l-xl">Waktu & IP</th>
                                    <th class="p-3">Jenis Peristiwa</th>
                                    <th class="p-3">Keparahan</th>
                                    <th class="p-3 text-right rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 font-medium">
                                @forelse($recentThreats as $threat)
                                    <tr>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-900 dark:text-white font-mono block">{{ $threat->ip_address }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $threat->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $threat->event_type }}</span>
                                            <span class="text-[10px] text-gray-400 truncate max-w-[180px] block">{{ $threat->user->name ?? 'Anonim / Guest' }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $threat->severity_badge }}">
                                                {{ $threat->severity }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right">
                                            @if(!$threat->is_resolved)
                                                <form action="{{ route('admin.security.resolve-threat', $threat->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-lg text-[9px] font-bold transition-all">
                                                        Resolve
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-emerald-500 font-bold">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-gray-400 italic">Tidak ditemukan log ancaman keamanan terkini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Blocked IPs Panel -->
                <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Daftar IP Address Terblokir</h3>
                        <button @click="blockIpModalOpen = true" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-[10px] uppercase transition-all shadow-md">
                            + Blokir IP Manual
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($blockedIps as $bIp)
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs">
                                <div>
                                    <strong class="font-mono text-rose-600 dark:text-rose-400 block font-bold">{{ $bIp->ip_address }}</strong>
                                    <span class="text-[10px] text-gray-500 italic block">{{ $bIp->reason ?? 'Tidak ada alasan' }}</span>
                                </div>
                                <form action="{{ route('admin.security.unblock-ip', $bIp->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[9px] font-bold transition-all">
                                        Unblock
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 text-xs italic">
                                Belum ada IP address yang diblokir.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

        <!-- Lockdown Modal Confirmation -->
        <div x-show="lockdownModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="lockdownModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-md transition-opacity" @click="lockdownModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="lockdownModalOpen" x-transition.scale class="inline-block align-bottom bg-gray-900 text-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-rose-500/40 p-6 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-500 border border-rose-500/30 flex items-center justify-center font-black text-2xl">
                            
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white leading-tight">
                                {{ $isLockdown ? 'Nonaktifkan System Lockdown?' : 'Konfirmasi Darurat System Lockdown' }}
                            </h3>
                            <p class="text-xs text-rose-400 mt-1 font-semibold">Tindakan ini memiliki dampak tinggi pada akses pengguna sistem SISTA.</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-300 leading-relaxed">
                        {{ $isLockdown 
                            ? 'Dengan menonaktifkan status Lockdown, rute publik dan akses pengguna umum akan dibuka kembali.' 
                            : 'Mengaktifkan Lockdown akan memutus akses seluruh rute siswa, guru, dan staf secara seketika. Hanya rute keamanan admin yang diizinkan.' }}
                    </p>

                    <form action="{{ route('admin.security.lockdown') }}" method="POST" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                        @csrf
                        <button type="button" @click="lockdownModalOpen = false" class="px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-rose-600/30">
                            Ya, Eksekusi Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Block IP Manual Modal -->
        <div x-show="blockIpModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="blockIpModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="blockIpModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="blockIpModalOpen" x-transition.scale class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Blokir IP Address Manual</h3>
                    
                    <form action="{{ route('admin.security.block-ip') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">IP Address</label>
                            <input type="text" name="ip_address" placeholder="Contoh: 192.168.1.100" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-3.5 py-2.5 font-mono focus:ring-2 focus:ring-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alasan Pemblokiran</label>
                            <input type="text" name="reason" placeholder="Contoh: Terdeteksi Brute Force Login 20x" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="blockIpModalOpen = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold">Blokir IP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
