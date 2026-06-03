<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-desktop text-indigo-400 mr-2"></i> {{ __('Monitoring Sesi Aktif') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.security.dashboard') }}" id="btn-security-dashboard" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-all duration-200 shadow-md flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke SIEM
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-lg"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Warning Notice Card -->
            <div class="bg-indigo-950/30 border border-indigo-500/30 rounded-2xl p-6 shadow-xl backdrop-blur-xl">
                <div class="flex">
                    <div class="flex-shrink-0 bg-indigo-500/10 p-3 rounded-xl border border-indigo-500/20">
                        <i class="fa-solid fa-shield-halved text-indigo-400 text-xl"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-md font-semibold text-indigo-200">Manajemen Akses Perangkat</h3>
                        <p class="text-sm text-slate-400 mt-1">Daftar di bawah ini mencakup seluruh sesi pengguna yang saat ini sedang aktif masuk ke sistem **SISTA**. Menekan tombol <strong>"Terminasi Sesi"</strong> akan menghapus otorisasi dari database secara instan. Pada request HTTP berikutnya, sistem akan secara otomatis melogout paksa perangkat tersebut demi alasan keamanan.</p>
                    </div>
                </div>
            </div>

            <!-- Active Sessions List Table -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-slate-200">
                        <i class="fa-solid fa-network-wired mr-2 text-indigo-400"></i> Perangkat Terhubung
                    </h3>
                    <span class="bg-indigo-500/10 text-indigo-400 text-xs px-3 py-1 rounded-full font-bold border border-indigo-500/25">
                        {{ $sessions->total() }} Sesi Terdeteksi
                    </span>
                </div>
                
                @if($sessions->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-display text-4xl text-slate-600 mb-3 block"></i>
                        <p>Tidak ada sesi aktif terdeteksi.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700/50">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Pengguna / Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Device / Perangkat</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">IP Address</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Aktivitas Terakhir</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 bg-slate-800/20">
                                @foreach($sessions as $session)
                                    @php
                                        $isCurrentUser = ($session->session_id === request()->session()->getId());
                                    @endphp
                                    <tr class="hover:bg-slate-700/20 transition-colors {{ $isCurrentUser ? 'bg-indigo-500/5 border-l-4 border-l-indigo-500' : '' }}">
                                        <!-- User & Role -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ strtoupper(substr($session->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-white flex items-center">
                                                        <span>{{ $session->user->name ?? 'Unknown' }}</span>
                                                        @if($isCurrentUser)
                                                            <span class="ml-2 bg-indigo-500/20 text-indigo-300 text-[10px] px-2 py-0.5 rounded-full font-bold border border-indigo-400/30">
                                                                Anda
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-slate-400 flex items-center space-x-1 mt-0.5">
                                                        <span class="capitalize text-indigo-400 font-semibold">{{ $session->user->role ?? 'N/A' }}</span>
                                                        <span>&bull;</span>
                                                        <span>{{ $session->user->email ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Device Type & Browser -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-200 flex items-center">
                                                @if($session->device_type === 'mobile')
                                                    <i class="fa-solid fa-mobile-screen-button text-teal-400 text-lg mr-2"></i>
                                                    <span>Smartphone</span>
                                                @elseif($session->device_type === 'tablet')
                                                    <i class="fa-solid fa-tablet-screen-button text-amber-400 text-lg mr-2"></i>
                                                    <span>Tablet</span>
                                                @else
                                                    <i class="fa-solid fa-desktop text-indigo-400 text-lg mr-2"></i>
                                                    <span>Desktop / Laptop</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 truncate max-w-[180px] mt-1 font-mono" title="{{ $session->user_agent }}">
                                                {{ $session->user_agent ?: 'Unknown User Agent' }}
                                            </div>
                                        </td>
                                        <!-- IP Address -->
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                            <div class="text-slate-300 font-semibold">{{ $session->ip_address }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">Indonesia (Lokal)</div>
                                        </td>
                                        <!-- Last Activity -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="text-slate-300 flex items-center">
                                                <i class="fa-solid fa-clock-rotate-left mr-1.5 text-slate-400 text-xs"></i>
                                                {{ $session->last_activity_at->diffForHumans() }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                                                ID: {{ substr($session->session_id, 0, 8) }}...
                                            </div>
                                        </td>
                                        <!-- Terminate Action -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <form action="{{ route('admin.security.sessions.terminate', $session->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan paksa sesi perangkat ini? Pengguna akan segera dikeluarkan secara otomatis.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" id="btn-terminate-{{ $session->id }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ $isCurrentUser ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-rose-500/35' : 'bg-rose-600/10 text-rose-400 border border-rose-500/20 hover:bg-rose-600 hover:text-white' }} transition-all duration-200">
                                                    <i class="fa-solid fa-power-off mr-1.5"></i> Putuskan Sesi
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($sessions->hasPages())
                        <div class="px-6 py-4 bg-slate-900/50 border-t border-slate-700/50">
                            {{ $sessions->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
