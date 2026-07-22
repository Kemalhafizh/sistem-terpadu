<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                    
                </span>
                {{ __('Log Penerima & Konfirmasi Pembacaan') }}
            </h2>
            <a href="{{ route('announcements.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Summary Info -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Pengumuman</span>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mt-1">{{ $announcement->title }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Diterbitkan oleh: {{ $announcement->creator->name }} • {{ $announcement->created_at->translatedFormat('d M Y H:i') }}</p>
                </div>
                
                @if($announcement->require_acknowledgement)
                    <span class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/50 dark:border-rose-900/30 text-rose-700 dark:text-rose-450 text-xs font-black uppercase tracking-wider rounded-xl">
                         Wajib Konfirmasi Pembacaan
                    </span>
                @else
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-650 dark:text-gray-400 text-xs font-black uppercase tracking-wider rounded-xl">
                        Info Umum
                    </span>
                @endif
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Target -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Penerima</span>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $totalTarget }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-450 rounded-2xl flex items-center justify-center text-2xl">
                        
                    </div>
                </div>

                <!-- Total Read -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Sudah Membaca</span>
                        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-450 mt-1">
                            {{ $totalRead }} 
                            <span class="text-sm font-bold text-gray-400 dark:text-gray-500">
                                ({{ $totalTarget > 0 ? round(($totalRead / $totalTarget) * 100) : 0 }}%)
                            </span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-450 rounded-2xl flex items-center justify-center text-2xl">
                        
                    </div>
                </div>

                <!-- Total Acknowledged -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Terkonfirmasi</span>
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $totalAcknowledged }}
                            <span class="text-sm font-bold text-gray-400 dark:text-gray-500">
                                ({{ $totalTarget > 0 ? round(($totalAcknowledged / $totalTarget) * 100) : 0 }}%)
                            </span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center text-2xl">
                        
                    </div>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-3xl border border-gray-100 dark:border-gray-700">
                <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750 flex items-center justify-between">
                    <h4 class="font-black text-sm text-gray-800 dark:text-white uppercase tracking-widest">Daftar Penerima Detail</h4>
                    <span class="text-xs text-gray-400 dark:text-gray-500 font-bold">Menampilkan {{ $targetUsers->count() }} orang</span>
                </div>
                
                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-750">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama User</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Peran (Role)</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Kelas</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status Baca</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Konfirmasi Pembacaan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($targetUsers as $targetUser)
                                @php
                                    $read = $reads->get($targetUser->id);
                                    $classroom = null;
                                    if ($targetUser->role === 'student') {
                                        $classroom = $targetUser->student?->classroom?->name;
                                    } elseif ($targetUser->role === 'parent') {
                                        $classroom = $targetUser->children->first()?->classroom?->name;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <!-- Name -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $targetUser->name }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">
                                            {{ $targetUser->email }}
                                        </div>
                                    </td>
                                    <!-- Role -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-100/30">
                                            {{ $targetUser->role }}
                                        </span>
                                    </td>
                                    <!-- Classroom -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-600 dark:text-gray-300">
                                        {{ $classroom ?? '-' }}
                                    </td>
                                    <!-- Read Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($read)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                 Dibaca
                                            </span>
                                            <div class="text-[10px] text-gray-450 dark:text-gray-500 mt-1">
                                                {{ $read->read_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                Belum Dibaca
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Acknowledged Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($announcement->require_acknowledgement)
                                            @if($read && $read->acknowledged_at)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-850 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                     Terkonfirmasi
                                                </span>
                                                <div class="text-[10px] text-gray-450 dark:text-gray-500 mt-1">
                                                    {{ $read->acknowledged_at->translatedFormat('d M Y H:i') }}
                                                </div>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                    Belum Konfirmasi
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                Tidak Diperlukan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-550 dark:text-gray-500 text-sm">
                                        Tidak ada target penerima yang terdaftar untuk filter ini.
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
